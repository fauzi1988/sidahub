<?php

namespace App\Http\Controllers;

use App\Models\JabatanPegawai;
use App\Models\ManajemenTtd;
use App\Models\Pegawai;
use App\Models\SuratKeluar;
use App\Models\SuratKeluarLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersuratanController extends Controller
{
    private const APPROVAL_STAGE_CONFIG = [
        'kabid' => [
            'status' => 'menunggu_review_substansi',
            'title' => 'Halaman Approve Kabid',
        ],
        'sekretariat' => [
            'status' => null,
            'statuses' => ['menunggu_verifikasi', 'disetujui'],
            'title' => 'Halaman Approve Sekretariat',
        ],
        'kadis' => [
            'status' => 'menunggu_ttd',
            'title' => 'Halaman Approve Kepala Dinas',
        ],
    ];

    public function index(Request $request): View
    {
        $flowRoles = $this->flowRoles($request);
        $query = $this->baseListQuery($request);
        $inboxMode = $request->boolean('inbox');
        if ($inboxMode && ! $request->filled('status')) {
            $inboxStatuses = $this->inboxStatuses($flowRoles);
            if (! empty($inboxStatuses)) {
                $query->whereIn('status', $inboxStatuses);
            }
        }

        $list = $query->paginate(10)->withQueryString();
        $statusOptions = SuratKeluar::statusOptions();
        $ttdOptions = $this->activeTtdOptions();

        $pageTitle = 'Persuratan (Surat Keluar)';
        $isApprovalPage = false;
        $currentApprovalStage = null;

        return view('admin.Kepegawaian.persuratan.surat_keluar.index', compact('list', 'statusOptions', 'flowRoles', 'inboxMode', 'pageTitle', 'isApprovalPage', 'ttdOptions', 'currentApprovalStage'));
    }

    public function approveKabid(Request $request): View
    {
        abort_unless($this->isKabid($request), 403, 'Halaman ini hanya untuk Kepala Bidang.');

        return $this->approvalStagePage($request, 'kabid');
    }

    public function approveSekretariat(Request $request): View
    {
        abort_unless($this->isSekretariat($request), 403, 'Halaman ini hanya untuk Sekretariat.');

        return $this->approvalStagePage($request, 'sekretariat');
    }

    public function approveKadis(Request $request): View
    {
        abort_unless($this->isKadis($request), 403, 'Halaman ini hanya untuk Kepala Dinas.');

        return $this->approvalStagePage($request, 'kadis');
    }

    public function create(): View
    {
        $pegawaiOptions = Pegawai::query()
            ->orderBy('nama_lengkap')
            ->get(['id_pegawai', 'nama_lengkap', 'nip']);
        $kadisPegawai = $this->resolveKadisPegawai();

        return view('admin.Kepegawaian.persuratan.surat_keluar.create', compact('pegawaiOptions', 'kadisPegawai'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        if (! isset($data['id_pegawai_penandatangan']) || ! $data['id_pegawai_penandatangan']) {
            $kadisPegawai = $this->resolveKadisPegawai();
            if ($kadisPegawai) {
                $data['id_pegawai_penandatangan'] = $kadisPegawai->id_pegawai;
            }
        }
        if (empty(trim((string) ($data['nomor_surat'] ?? '')))) {
            $data['nomor_surat'] = null;
        }
        SuratKeluar::create($data);

        return redirect()
            ->route('persuratan-surat-keluar.index')
            ->with('success', 'Surat keluar berhasil ditambahkan.');
    }

    public function show(SuratKeluar $persuratan): View
    {
        $persuratan->load([
            'pengusul:id_pegawai,nama_lengkap,nip',
            'penandatangan:id_pegawai,nama_lengkap,nip',
            'logs.user:id,name',
            'ttdManagement:id_ttd,nama_ttd,jenis_ttd',
        ]);
        $flowRoles = $this->flowRoles(request());
        $ttdOptions = $this->activeTtdOptions();

        return view('admin.Kepegawaian.persuratan.surat_keluar.show', compact('persuratan', 'flowRoles', 'ttdOptions'));
    }

    public function print(SuratKeluar $persuratan)
    {
        abort_unless($persuratan->canBePrinted(), 403, 'Surat hanya dapat dicetak setelah ditandatangani.');

        $persuratan->load([
            'pengusul:id_pegawai,nama_lengkap,nip',
            'penandatangan:id_pegawai,nama_lengkap,nip',
        ]);

        $pdf = Pdf::loadView('admin.Kepegawaian.persuratan.surat_keluar.print', [
            'persuratan' => $persuratan,
        ])->setPaper('A4', 'portrait');

        $filename = 'surat-keluar-'.($persuratan->nomor_surat
            ? preg_replace('/[^A-Za-z0-9\-]+/', '-', $persuratan->nomor_surat)
            : $persuratan->id_surat_keluar).'.pdf';

        return $pdf->stream($filename);
    }

    public function submit(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isOperator($request), 403);
        abort_unless($persuratan->status === 'draft' || $persuratan->status === 'revisi_substansi', 422, 'Status surat tidak valid untuk dikirim.');

        $from = $persuratan->status;
        $persuratan->update([
            'status' => 'menunggu_review_substansi',
            'submitted_at' => now(),
        ]);

        $this->logFlow($persuratan, $request, 'submit_ke_kabid', $from, 'menunggu_review_substansi', $request->input('note'));

        return back()->with('success', 'Surat berhasil dikirim ke Kepala Bidang untuk koreksi.');
    }

    public function kabidApprove(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKabid($request), 403);
        abort_unless($persuratan->status === 'menunggu_review_substansi', 422, 'Status surat tidak valid untuk approval Kabid.');

        $from = $persuratan->status;
        $persuratan->update([
            'status' => 'menunggu_verifikasi',
            'reviewed_at' => now(),
            'reviewed_by_kabid_user_id' => $request->user()->id,
        ]);

        $this->logFlow($persuratan, $request, 'approve_kabid', $from, 'menunggu_verifikasi', $request->input('note'));

        return back()->with('success', 'Surat disetujui Kepala Bidang dan diteruskan ke Sekretariat.');
    }

    public function kabidRevise(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKabid($request), 403);
        abort_unless($persuratan->status === 'menunggu_review_substansi', 422, 'Status surat tidak valid untuk revisi Kabid.');

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $from = $persuratan->status;
        $persuratan->update([
            'status' => 'revisi_substansi',
        ]);

        $this->logFlow($persuratan, $request, 'revisi_kabid', $from, 'revisi_substansi', $data['note']);

        return back()->with('success', 'Surat dikembalikan ke operator untuk revisi.');
    }

    public function sekretariatForward(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isSekretariat($request), 403);
        abort_unless($persuratan->status === 'menunggu_verifikasi', 422, 'Status surat tidak valid untuk Sekretariat.');

        $from = $persuratan->status;
        $persuratan->update([
            'status' => 'menunggu_ttd',
            'verified_at' => now(),
            'forwarded_to_kadis_at' => now(),
            'verified_by_sekretariat_user_id' => $request->user()->id,
        ]);

        $this->logFlow($persuratan, $request, 'teruskan_sekretariat_ke_kadis', $from, 'menunggu_ttd', $request->input('note'));

        return back()->with('success', 'Surat diteruskan ke Kepala Dinas untuk penandatanganan.');
    }

    public function kadisSign(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKadis($request), 403);
        abort_unless($persuratan->status === 'menunggu_ttd', 422, 'Status surat tidak valid untuk tanda tangan Kepala Dinas.');
        $data = $request->validate([
            'jenis_ttd' => ['required', Rule::in(array_keys(SuratKeluar::jenisTtdOptions()))],
            'ttd_management_id' => ['required', 'integer', 'exists:manajemen_ttd,id_ttd'],
        ]);
        $ttd = ManajemenTtd::query()
            ->where('id_ttd', $data['ttd_management_id'])
            ->where('is_active', true)
            ->first();
        abort_unless($ttd !== null, 422, 'Master TTD tidak ditemukan atau tidak aktif.');
        abort_unless($ttd->jenis_ttd === $data['jenis_ttd'], 422, 'Jenis TTD tidak sesuai dengan master TTD yang dipilih.');

        $from = $persuratan->status;
        $persuratan->update([
            'status' => 'disetujui',
            'signed_at' => now(),
            'signed_by_kadis_user_id' => $request->user()->id,
            'id_pegawai_penandatangan' => $request->user()->id_pegawai ?: $persuratan->id_pegawai_penandatangan,
            'jenis_ttd' => $data['jenis_ttd'],
            'ttd_management_id' => $ttd->id_ttd,
        ]);

        $this->logFlow($persuratan, $request, 'approve_kadis', $from, 'disetujui', 'Jenis TTD: '.$data['jenis_ttd'].' | Master: '.$ttd->nama_ttd);

        return back()->with('success', 'Surat disetujui Kepala Dinas dan dikembalikan ke Sekretariat untuk penomoran.');
    }

    public function sekretariatNumberAndSend(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isSekretariat($request), 403);
        abort_unless($persuratan->status === 'disetujui', 422, 'Status surat tidak valid untuk penomoran Sekretariat.');

        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:120', Rule::unique('surat_keluar', 'nomor_surat')->ignore($persuratan->id_surat_keluar, 'id_surat_keluar')],
            'tanggal_kirim' => ['nullable', 'date', 'after_or_equal:tanggal_surat'],
        ]);

        $from = $persuratan->status;
        $persuratan->update([
            'nomor_surat' => $data['nomor_surat'],
            'tanggal_kirim' => $data['tanggal_kirim'] ?? now()->toDateString(),
            'status' => 'dikirim',
        ]);

        $this->logFlow($persuratan, $request, 'sekretariat_nomor_dan_kirim', $from, 'dikirim', $request->input('note'));

        return back()->with('success', 'Nomor surat berhasil diberikan, surat otomatis berstatus dikirim dan siap dicetak.');
    }

    public function edit(SuratKeluar $persuratan): View
    {
        $persuratan->load([
            'pengusul:id_pegawai,nama_lengkap,nip',
            'penandatangan:id_pegawai,nama_lengkap,nip',
        ]);

        $pegawaiOptions = Pegawai::query()
            ->orderBy('nama_lengkap')
            ->get(['id_pegawai', 'nama_lengkap', 'nip']);

        return view('admin.Kepegawaian.persuratan.surat_keluar.edit', compact('persuratan', 'pegawaiOptions'));
    }

    public function update(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        $data = $request->validate($this->rules($persuratan));
        if (empty(trim((string) ($data['nomor_surat'] ?? '')))) {
            $data['nomor_surat'] = null;
        }
        $persuratan->update($data);

        return redirect()
            ->route('persuratan-surat-keluar.index')
            ->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $persuratan): RedirectResponse
    {
        $persuratan->delete();

        return redirect()
            ->route('persuratan-surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dihapus.');
    }

    private function rules(?SuratKeluar $suratKeluar = null): array
    {
        $nomorRule = ['nullable', 'string', 'max:120', Rule::unique('surat_keluar', 'nomor_surat')];
        if ($suratKeluar) {
            $nomorRule[3] = Rule::unique('surat_keluar', 'nomor_surat')
                ->ignore($suratKeluar->id_surat_keluar, 'id_surat_keluar');
        }

        return [
            'nomor_surat' => $nomorRule,
            'tanggal_surat' => ['required', 'date'],
            'tanggal_kirim' => ['nullable', 'date', 'after_or_equal:tanggal_surat'],
            'perihal' => ['required', 'string', 'max:255'],
            'tujuan_surat' => ['required', 'string', 'max:255'],
            'alamat_tujuan' => ['nullable', 'string'],
            'jenis_surat' => ['required', Rule::in(array_keys(SuratKeluar::jenisSuratOptions()))],
            'sifat_surat' => ['required', Rule::in(array_keys(SuratKeluar::sifatSuratOptions()))],
            'prioritas' => ['required', Rule::in(array_keys(SuratKeluar::prioritasOptions()))],
            'status' => ['required', Rule::in(array_keys(SuratKeluar::statusOptions()))],
            'id_pegawai_pengusul' => ['nullable', 'exists:pegawai,id_pegawai'],
            'id_pegawai_penandatangan' => ['nullable', 'exists:pegawai,id_pegawai'],
            'ringkasan' => ['nullable', 'string'],
            'isi_surat' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    private function flowRoles(Request $request): array
    {
        return [
            'operator' => $this->isOperator($request),
            'kabid' => $this->isKabid($request),
            'sekretariat' => $this->isSekretariat($request),
            'kadis' => $this->isKadis($request),
        ];
    }

    private function isOperator(Request $request): bool
    {
        return $this->userHasPermission($request, 'kepegawaian.persuratan.surat_keluar');
    }

    private function isKabid(Request $request): bool
    {
        return $this->userHasPermission($request, 'kepegawaian.persuratan.approve_kabid');
    }

    private function isSekretariat(Request $request): bool
    {
        return $this->userHasPermission($request, 'kepegawaian.persuratan.approve_sekretariat');
    }

    private function isKadis(Request $request): bool
    {
        return $this->userHasPermission($request, 'kepegawaian.persuratan.approve_kadis');
    }

    private function userHasPermission(Request $request, string $key): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }
        if ($user->is_super_admin) {
            return true;
        }

        return $user->hasPermission($key);
    }

    private function inboxStatuses(array $flowRoles): array
    {
        if (($flowRoles['kadis'] ?? false) === true) {
            return ['menunggu_ttd'];
        }
        if (($flowRoles['sekretariat'] ?? false) === true) {
            return ['menunggu_verifikasi', 'disetujui'];
        }
        if (($flowRoles['kabid'] ?? false) === true) {
            return ['menunggu_review_substansi'];
        }
        if (($flowRoles['operator'] ?? false) === true) {
            return ['draft', 'revisi_substansi'];
        }

        return [];
    }

    private function baseListQuery(Request $request)
    {
        $query = SuratKeluar::query()
            ->with([
                'pengusul:id_pegawai,nama_lengkap,nip',
                'penandatangan:id_pegawai,nama_lengkap,nip',
            ])
            ->latest('id_surat_keluar');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_surat', 'like', '%'.$q.'%')
                    ->orWhere('perihal', 'like', '%'.$q.'%')
                    ->orWhere('tujuan_surat', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query;
    }

    private function approvalStagePage(Request $request, string $stage): View
    {
        $config = self::APPROVAL_STAGE_CONFIG[$stage];
        $flowRoles = $this->flowRoles($request);
        $query = $this->baseListQuery($request);

        if (! $request->filled('status')) {
            $statuses = $config['statuses'] ?? [$config['status']];
            $statuses = array_values(array_filter($statuses));
            if (count($statuses) === 1) {
                $query->where('status', $statuses[0]);
            } elseif (count($statuses) > 1) {
                $query->whereIn('status', $statuses);
            }
        }

        $list = $query->paginate(10)->withQueryString();
        $statusOptions = SuratKeluar::statusOptions();
        $ttdOptions = $this->activeTtdOptions();
        $inboxMode = false;
        $pageTitle = $config['title'];
        $isApprovalPage = true;
        $currentApprovalStage = $stage;

        return view('admin.Kepegawaian.persuratan.surat_keluar.index', compact('list', 'statusOptions', 'flowRoles', 'inboxMode', 'pageTitle', 'isApprovalPage', 'ttdOptions', 'currentApprovalStage'));
    }

    private function activeTtdOptions()
    {
        return ManajemenTtd::query()
            ->where('is_active', true)
            ->orderBy('jenis_ttd')
            ->orderBy('nama_ttd')
            ->get(['id_ttd', 'nama_ttd', 'jenis_ttd', 'pemilik_ttd', 'jabatan_pemilik']);
    }

    private function resolveKadisPegawai(): ?Pegawai
    {
        $idPegawai = JabatanPegawai::query()
            ->where('status_jabatan', 'Aktif')
            ->where(function ($q) {
                $q->where('jabatan', 'like', '%kepala dinas%')
                    ->orWhere('jabatan', 'like', '%kadis%');
            })
            ->orderByDesc('tmt')
            ->value('id_pegawai');

        if (! $idPegawai) {
            return null;
        }

        return Pegawai::query()->find($idPegawai, ['id_pegawai', 'nama_lengkap', 'nip']);
    }

    private function logFlow(
        SuratKeluar $persuratan,
        Request $request,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $note = null
    ): void {
        SuratKeluarLog::create([
            'surat_keluar_id' => $persuratan->id_surat_keluar,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }
}
