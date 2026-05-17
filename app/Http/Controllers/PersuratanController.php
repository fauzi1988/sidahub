<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratKeluarRequest;
use App\Http\Requests\SuratKeluarKadisSignRequest;
use App\Http\Requests\SuratKeluarSekretariatNumberRequest;
use App\Http\Requests\SuratKeluarWorkflowNoteRequest;
use App\Http\Requests\UpdateSuratKeluarRequest;
use App\Models\JabatanPegawai;
use App\Models\ManajemenTtd;
use App\Models\Pegawai;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\SuratKeluarNomorService;
use App\Services\SuratKeluarWorkflowService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;

class PersuratanController extends Controller
{
    private const APPROVAL_STAGE_CONFIG = [
        'kabid' => [
            'statuses' => ['menunggu_review_substansi'],
            'title' => 'Halaman Approve Kabid',
        ],
        'sekretariat' => [
            'statuses' => ['menunggu_verifikasi', 'disetujui'],
            'title' => 'Halaman Approve Sekretariat',
        ],
        'kadis' => [
            'statuses' => ['menunggu_ttd'],
            'title' => 'Halaman Approve Kepala Dinas',
        ],
    ];

    public function __construct(
        private readonly SuratKeluarWorkflowService $workflow,
        private readonly SuratKeluarNomorService $nomorService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SuratKeluar::class);

        $flowRoles = $this->flowRoles($request);
        $query = $this->baseListQuery($request, $flowRoles);
        $inboxMode = $request->boolean('inbox');

        if ($inboxMode && ! $request->filled('status')) {
            $inboxStatuses = $this->inboxStatuses($flowRoles);
            if (! empty($inboxStatuses)) {
                $query->whereIn('status', $inboxStatuses);
            }
        }

        return $this->listView($request, $query, $flowRoles, $inboxMode, 'Persuratan (Surat Keluar)', false, null);
    }

    public function approveKabid(Request $request): View
    {
        abort_unless($this->isKabid($request), 403);

        return $this->approvalStagePage($request, 'kabid');
    }

    public function approveSekretariat(Request $request): View
    {
        abort_unless($this->isSekretariat($request), 403);

        return $this->approvalStagePage($request, 'sekretariat');
    }

    public function approveKadis(Request $request): View
    {
        abort_unless($this->isKadis($request), 403);

        return $this->approvalStagePage($request, 'kadis');
    }

    public function create(): View
    {
        $this->authorize('create', SuratKeluar::class);

        $pegawaiOptions = Pegawai::query()->orderBy('nama_lengkap')->get(['id_pegawai', 'nama_lengkap', 'nip']);
        $kadisPegawai = $this->resolveKadisPegawai();
        $isiTemplates = SuratKeluar::isiTemplates();
        $userPegawai = auth()->user()?->pegawai;

        return view('admin.Kepegawaian.persuratan.surat_keluar.create', compact(
            'pegawaiOptions',
            'kadisPegawai',
            'isiTemplates',
            'userPegawai',
        ));
    }

    public function store(StoreSuratKeluarRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $data['status'] = 'draft';
        $data['nomor_surat'] = null;
        $data['created_by_user_id'] = $user?->id;
        $data['id_pegawai_pengusul'] = $data['id_pegawai_pengusul'] ?? $user?->id_pegawai;
        $data['unit_kerja'] = $this->resolveUnitKerja((int) ($data['id_pegawai_pengusul'] ?? 0));
        $data['lampiran'] = $this->storeLampiran($request, []);

        if (empty($data['id_pegawai_penandatangan'])) {
            $kadis = $this->resolveKadisPegawai();
            if ($kadis) {
                $data['id_pegawai_penandatangan'] = $kadis->id_pegawai;
            }
        }

        SuratKeluar::create($data);

        return redirect()
            ->route('persuratan-surat-keluar.index', ['inbox' => 1])
            ->with('success', 'Draft surat keluar berhasil dibuat.');
    }

    public function show(SuratKeluar $persuratan): View
    {
        $this->authorize('view', $persuratan);

        $persuratan->load([
            'pengusul:id_pegawai,nama_lengkap,nip',
            'penandatangan:id_pegawai,nama_lengkap,nip',
            'createdBy:id,name',
            'logs.user:id,name',
            'ttdManagement:id_ttd,nama_ttd,jenis_ttd,file_ttd',
            'suratMasuk',
        ]);

        $flowRoles = $this->flowRoles(request());
        $ttdOptions = $this->activeTtdOptions();
        $suggestedNomor = $this->nomorService->suggestNext(
            (int) optional($persuratan->tanggal_surat)->format('Y')
        );

        return view('admin.Kepegawaian.persuratan.surat_keluar.show', compact(
            'persuratan',
            'flowRoles',
            'ttdOptions',
            'suggestedNomor',
        ));
    }

    public function verify(string $code): View
    {
        $persuratan = SuratKeluar::query()
            ->where('verification_code', $code)
            ->whereIn('status', ['dikirim', 'diarsipkan'])
            ->firstOrFail();

        return view('admin.Kepegawaian.persuratan.surat_keluar.verify', compact('persuratan'));
    }

    public function print(SuratKeluar $persuratan)
    {
        $this->authorize('view', $persuratan);
        abort_unless($persuratan->canBePrinted(), 403, 'Surat hanya dapat dicetak setelah dinomori dan berstatus dikirim.');

        $persuratan->load([
            'pengusul:id_pegawai,nama_lengkap,nip',
            'penandatangan:id_pegawai,nama_lengkap,nip',
            'ttdManagement:id_ttd,nama_ttd,jenis_ttd,file_ttd',
        ]);

        $pdf = Pdf::loadView('admin.Kepegawaian.persuratan.surat_keluar.print', [
            'persuratan' => $persuratan,
            'verifyUrl' => $persuratan->verification_code
                ? route('persuratan-surat-keluar.verify', $persuratan->verification_code)
                : null,
        ])->setPaper('A4', 'portrait');

        $filename = 'surat-keluar-'.($persuratan->nomor_surat
            ? preg_replace('/[^A-Za-z0-9\-]+/', '-', $persuratan->nomor_surat)
            : $persuratan->id_surat_keluar).'.pdf';

        return $pdf->stream($filename);
    }

    public function submit(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        $this->authorize('submit', $persuratan);

        try {
            $this->workflow->submit($persuratan, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat berhasil dikirim ke Kepala Bidang.');
    }

    public function kabidApprove(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKabid($request), 403);

        try {
            $this->workflow->kabidApprove($persuratan, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat disetujui Kabid dan diteruskan ke Sekretariat.');
    }

    public function kabidRevise(SuratKeluarWorkflowNoteRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKabid($request), 403);

        try {
            $this->workflow->kabidRevise($persuratan, $request, $request->validated('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat dikembalikan ke operator untuk revisi substansi.');
    }

    public function sekretariatForward(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isSekretariat($request), 403);

        try {
            $this->workflow->sekretariatForward($persuratan, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat diteruskan ke Kepala Dinas.');
    }

    public function sekretariatRevise(SuratKeluarWorkflowNoteRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isSekretariat($request), 403);

        try {
            $this->workflow->sekretariatRevise($persuratan, $request, $request->validated('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat dikembalikan untuk revisi administrasi.');
    }

    public function kadisSign(SuratKeluarKadisSignRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        $data = $request->validated();
        $ttd = ManajemenTtd::query()
            ->where('id_ttd', $data['ttd_management_id'])
            ->where('is_active', true)
            ->first();

        abort_unless($ttd !== null, 422, 'Master TTD tidak ditemukan atau tidak aktif.');
        abort_unless($ttd->jenis_ttd === $data['jenis_ttd'], 422, 'Jenis TTD tidak sesuai master.');

        try {
            $this->workflow->kadisSign($persuratan, $request, [
                'jenis_ttd' => $data['jenis_ttd'],
                'ttd_management_id' => $ttd->id_ttd,
            ], $data['note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat ditandatangani Kadis. Menunggu penomoran Sekretariat.');
    }

    public function kadisRevise(SuratKeluarWorkflowNoteRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        abort_unless($this->isKadis($request), 403);

        try {
            $this->workflow->kadisRevise($persuratan, $request, $request->validated('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat dikembalikan ke Sekretariat untuk perbaikan.');
    }

    public function sekretariatNumberAndSend(SuratKeluarSekretariatNumberRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        $data = $request->validated();
        $nomor = $data['nomor_surat'];

        if ($request->boolean('use_suggested')) {
            $nomor = $this->nomorService->suggestNext((int) optional($persuratan->tanggal_surat)->format('Y'));
        }

        try {
            $this->workflow->sekretariatNumberAndSend(
                $persuratan,
                $request,
                $nomor,
                $data['tanggal_kirim'] ?? null,
                $data['note'] ?? null,
            );
            $this->syncSuratMasukFromKeluar($persuratan->fresh());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Nomor surat diberikan. Surat siap dicetak.');
    }

    public function archive(Request $request, SuratKeluar $persuratan): RedirectResponse
    {
        $this->authorize('archive', $persuratan);

        try {
            $this->workflow->archive($persuratan, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat berhasil diarsipkan.');
    }

    public function cancel(SuratKeluarWorkflowNoteRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        $this->authorize('cancel', $persuratan);

        try {
            $this->workflow->cancel($persuratan, $request, $request->validated('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return redirect()
            ->route('persuratan-surat-keluar.index')
            ->with('success', 'Surat dibatalkan.');
    }

    public function suggestNomor(SuratKeluar $persuratan)
    {
        abort_unless($this->isSekretariat(request()), 403);

        return response()->json([
            'nomor' => $this->nomorService->suggestNext((int) optional($persuratan->tanggal_surat)->format('Y')),
        ]);
    }

    public function edit(SuratKeluar $persuratan): View
    {
        $this->authorize('update', $persuratan);

        $persuratan->load(['pengusul:id_pegawai,nama_lengkap,nip', 'penandatangan:id_pegawai,nama_lengkap,nip']);
        $pegawaiOptions = Pegawai::query()->orderBy('nama_lengkap')->get(['id_pegawai', 'nama_lengkap', 'nip']);
        $isiTemplates = SuratKeluar::isiTemplates();

        return view('admin.Kepegawaian.persuratan.surat_keluar.edit', compact('persuratan', 'pegawaiOptions', 'isiTemplates'));
    }

    public function update(UpdateSuratKeluarRequest $request, SuratKeluar $persuratan): RedirectResponse
    {
        $data = $request->validated();
        unset($data['hapus_lampiran']);

        $lampiran = $persuratan->lampiran ?? [];
        $lampiran = $this->removeLampiran($lampiran, $request->input('hapus_lampiran', []));
        $data['lampiran'] = $this->storeLampiran($request, $lampiran);
        $data['id_pegawai_pengusul'] = $data['id_pegawai_pengusul'] ?? $persuratan->id_pegawai_pengusul;
        $data['unit_kerja'] = $this->resolveUnitKerja((int) $data['id_pegawai_pengusul']);

        $persuratan->update($data);
        $this->workflow->logContentEdit($persuratan, $request);

        return redirect()
            ->route('persuratan-surat-keluar.show', $persuratan)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $persuratan): RedirectResponse
    {
        $this->authorize('delete', $persuratan);

        foreach ($persuratan->lampiran ?? [] as $file) {
            if (! empty($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $persuratan->delete();

        return redirect()
            ->route('persuratan-surat-keluar.index')
            ->with('success', 'Surat keluar berhasil dihapus.');
    }

    private function syncSuratMasukFromKeluar(SuratKeluar $surat): void
    {
        if ($surat->suratMasuk) {
            $surat->suratMasuk->update([
                'nomor_surat' => $surat->nomor_surat,
                'tanggal_surat' => $surat->tanggal_surat,
                'perihal' => $surat->perihal,
            ]);

            return;
        }

        SuratMasuk::create([
            'nomor_surat' => $surat->nomor_surat,
            'tanggal_surat' => $surat->tanggal_surat,
            'tanggal_terima' => $surat->tanggal_kirim,
            'perihal' => $surat->perihal,
            'pengirim' => 'Dinas Perhubungan Kab. Haltim',
            'sifat_surat' => $surat->sifat_surat,
            'surat_keluar_id' => $surat->id_surat_keluar,
            'ringkasan' => $surat->ringkasan,
            'lampiran' => $surat->lampiran,
        ]);
    }

    /**
     * @param  list<array{path: string, name: string, size?: int}>  $existing
     * @return list<array{path: string, name: string, size: int}>
     */
    private function storeLampiran(Request $request, array $existing): array
    {
        if (! $request->hasFile('lampiran')) {
            return $existing;
        }

        foreach ($request->file('lampiran') as $file) {
            if (! $file) {
                continue;
            }
            $path = $file->store('surat-keluar/lampiran', 'public');
            $existing[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ];
        }

        return $existing;
    }

    /**
     * @param  list<array{path: string, name: string, size?: int}>  $lampiran
     * @param  list<string>  $pathsToRemove
     * @return list<array{path: string, name: string, size?: int}>
     */
    private function removeLampiran(array $lampiran, array $pathsToRemove): array
    {
        return array_values(array_filter($lampiran, function (array $file) use ($pathsToRemove) {
            if (in_array($file['path'] ?? '', $pathsToRemove, true)) {
                Storage::disk('public')->delete($file['path']);

                return false;
            }

            return true;
        }));
    }

    private function resolveUnitKerja(int $idPegawai): ?string
    {
        if (! $idPegawai) {
            return null;
        }

        return JabatanPegawai::query()
            ->where('id_pegawai', $idPegawai)
            ->where('status_jabatan', 'Aktif')
            ->orderByDesc('tmt')
            ->value('unit_kerja');
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

        return $user->is_super_admin || $user->hasPermission($key);
    }

    /**
     * @return list<string>
     */
    private function inboxStatuses(array $flowRoles): array
    {
        if ($flowRoles['kadis'] ?? false) {
            return ['menunggu_ttd'];
        }
        if ($flowRoles['sekretariat'] ?? false) {
            return ['menunggu_verifikasi', 'disetujui'];
        }
        if ($flowRoles['kabid'] ?? false) {
            return ['menunggu_review_substansi'];
        }
        if ($flowRoles['operator'] ?? false) {
            return ['draft', 'revisi_substansi', 'revisi_admin'];
        }

        return [];
    }

    private function baseListQuery(Request $request, array $flowRoles)
    {
        $query = SuratKeluar::query()
            ->with([
                'pengusul:id_pegawai,nama_lengkap,nip',
                'penandatangan:id_pegawai,nama_lengkap,nip',
            ])
            ->latest('id_surat_keluar');

        if (($flowRoles['operator'] ?? false) && ! ($flowRoles['kabid'] ?? false) && ! ($flowRoles['sekretariat'] ?? false) && ! ($flowRoles['kadis'] ?? false)) {
            $user = $request->user();
            $query->forInboxOperator($user?->id_pegawai, $user?->id);
        }

        if ($request->filled('unit_kerja')) {
            $query->where('unit_kerja', $request->string('unit_kerja'));
        }

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
        $query = $this->baseListQuery($request, $flowRoles);

        if (! $request->filled('status')) {
            $query->whereIn('status', $config['statuses']);
        }

        return $this->listView($request, $query, $flowRoles, false, $config['title'], true, $stage);
    }

    private function listView(
        Request $request,
        $query,
        array $flowRoles,
        bool $inboxMode,
        string $pageTitle,
        bool $isApprovalPage,
        ?string $currentApprovalStage,
    ): View {
        $list = $query->paginate(10)->withQueryString();

        return view('admin.Kepegawaian.persuratan.surat_keluar.index', [
            'list' => $list,
            'statusOptions' => SuratKeluar::statusOptions(),
            'flowRoles' => $flowRoles,
            'inboxMode' => $inboxMode,
            'pageTitle' => $pageTitle,
            'isApprovalPage' => $isApprovalPage,
            'currentApprovalStage' => $currentApprovalStage,
        ]);
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
}
