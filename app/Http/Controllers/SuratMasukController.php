<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSuratMasukRequest;
use App\Http\Requests\SuratMasukCompleteDisposisiRequest;
use App\Http\Requests\SuratMasukKadisDisposisiRequest;
use App\Http\Requests\UpdateSuratMasukRequest;
use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\SuratMasukDisposisi;
use App\Models\SuratMasukLog;
use App\Services\SuratMasukWorkflowService;
use App\Support\PersuratanMasukPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratMasukController extends Controller
{
    private const STAGE_CONFIG = [
        'sekretariat' => [
            'statuses' => ['tercatat', 'agenda'],
            'title' => 'Proses Sekretariat',
            'workflow' => 'sekretariat',
        ],
        'kadis' => [
            'statuses' => ['menunggu_disposisi_kadis'],
            'title' => 'Disposisi Kadis',
            'workflow' => 'kadis',
        ],
        'unit' => [
            'statuses' => ['disposisi_ke_unit', 'proses'],
            'title' => 'Tindak Lanjut Unit',
            'workflow' => 'unit',
        ],
    ];

    public function __construct(
        private readonly SuratMasukWorkflowService $workflow,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewDaftar', SuratMasuk::class);

        $query = $this->baseListQuery($request);
        $list = $query->latest('id_surat_masuk')->paginate(10)->withQueryString();

        return $this->listView($request, $list, 'Daftar Surat Masuk', false, null, 'persuratan-masuk.index');
    }

    public function prosesSekretariat(Request $request): View
    {
        abort_unless($this->isSekretariatOperator($request), 403);

        return $this->stageListPage($request, 'sekretariat');
    }

    public function disposisiKadis(Request $request): View
    {
        abort_unless($this->isKadis($request), 403);

        return $this->stageListPage($request, 'kadis');
    }

    public function tindakLanjutUnit(Request $request): View
    {
        abort_unless($this->isKabid($request), 403);

        return $this->stageListPage($request, 'unit');
    }

    public function arsip(Request $request): View
    {
        $this->authorize('viewDaftar', SuratMasuk::class);

        $query = $this->baseListQuery($request);
        if (! $request->filled('status')) {
            $query->whereIn('status', ['selesai', 'diarsipkan']);
        }

        $list = $query->latest('id_surat_masuk')->paginate(10)->withQueryString();

        return $this->listView($request, $list, 'Arsip Surat Masuk', false, null, 'persuratan-masuk.arsip');
    }

    public function create(): View
    {
        $this->authorize('create', SuratMasuk::class);

        $sifatOptions = SuratMasuk::sifatSuratOptions();

        return view('admin.Kepegawaian.persuratan.surat_masuk.create', compact('sifatOptions'));
    }

    public function store(StoreSuratMasukRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $lampiran = $this->storeLampiran($request, []);
        unset($data['lampiran']);

        $surat = SuratMasuk::create([
            ...$data,
            'lampiran' => $lampiran ?: null,
            'status' => 'tercatat',
            'created_by_user_id' => $request->user()?->id,
        ]);

        SuratMasukLog::create([
            'surat_masuk_id' => $surat->id_surat_masuk,
            'user_id' => $request->user()?->id,
            'action' => 'catat',
            'from_status' => null,
            'to_status' => 'tercatat',
            'note' => 'Pencatatan surat masuk',
        ]);

        return redirect()
            ->route('persuratan-masuk.show', ['surat_masuk' => $surat, 'context' => 'sekretariat'])
            ->with('success', 'Surat masuk berhasil dicatat.');
    }

    public function show(Request $request, SuratMasuk $surat_masuk): View
    {
        $this->authorize('view', $surat_masuk);

        $request->user()
            ?->unreadNotifications()
            ->where('data->surat_masuk_id', $surat_masuk->id_surat_masuk)
            ->get()
            ->markAsRead();

        $context = $this->resolveWorkflowContext($request);

        $surat_masuk->load([
            'createdBy:id,name',
            'disposisi.toPegawai:id_pegawai,nama_lengkap',
            'disposisi.fromUser:id,name',
            'logs.user:id,name',
            'suratKeluarBalasan:id_surat_keluar,nomor_surat,perihal,status',
        ]);

        $user = $request->user();
        $myActiveDisposisi = collect();
        if ($user && $context === 'unit') {
            $myActiveDisposisi = $surat_masuk->disposisi
                ->where('status', 'aktif')
                ->filter(fn (SuratMasukDisposisi $d) => $this->disposisiMatchesUser($user, $d));
        }

        $pegawaiOptions = Pegawai::query()->orderBy('nama_lengkap')->get(['id_pegawai', 'nama_lengkap', 'nip']);
        $unitOptions = JabatanPegawai::query()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct()
            ->orderBy('unit_kerja')
            ->pluck('unit_kerja');

        return view('admin.Kepegawaian.persuratan.surat_masuk.show', [
            'surat' => $surat_masuk,
            'workflowContext' => $context,
            'backRoute' => $this->backRouteForContext($context),
            'pegawaiOptions' => $pegawaiOptions,
            'unitOptions' => $unitOptions,
            'myActiveDisposisi' => $myActiveDisposisi,
        ]);
    }

    public function edit(SuratMasuk $surat_masuk): View
    {
        $this->authorize('update', $surat_masuk);

        $sifatOptions = SuratMasuk::sifatSuratOptions();
        $suratKeluarBalasanOptions = SuratKeluar::query()
            ->where('status', 'dikirim')
            ->orderByDesc('id_surat_keluar')
            ->limit(200)
            ->get(['id_surat_keluar', 'nomor_surat', 'perihal']);

        return view('admin.Kepegawaian.persuratan.surat_masuk.edit', [
            'surat' => $surat_masuk,
            'sifatOptions' => $sifatOptions,
            'suratKeluarBalasanOptions' => $suratKeluarBalasanOptions,
        ]);
    }

    public function update(UpdateSuratMasukRequest $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $data = $request->validated();
        $lampiran = $surat_masuk->lampiran ?? [];
        $lampiran = $this->removeLampiran($lampiran, $request->input('hapus_lampiran', []));
        $lampiran = $this->storeLampiran($request, $lampiran);
        unset($data['lampiran'], $data['hapus_lampiran']);

        $surat_masuk->update([
            ...$data,
            'lampiran' => $lampiran ?: null,
        ]);

        $this->workflow->logEdit($surat_masuk, $request);

        return redirect()
            ->route('persuratan-masuk.show', ['surat_masuk' => $surat_masuk, 'context' => 'sekretariat'])
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function destroy(SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('delete', $surat_masuk);

        foreach ($surat_masuk->lampiran ?? [] as $file) {
            if (! empty($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $surat_masuk->delete();

        return redirect()
            ->route('persuratan-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function setAgenda(Request $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('forwardToKadis', $surat_masuk);

        $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->workflow->setAgenda($surat_masuk, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Nomor agenda berhasil diberikan.');
    }

    public function forwardToKadis(Request $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('forwardToKadis', $surat_masuk);

        $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->workflow->forwardToKadis($surat_masuk, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return redirect()
            ->route('persuratan-masuk.disposisi-kadis')
            ->with('success', 'Surat diteruskan ke Kadis untuk disposisi.');
    }

    public function kadisDispose(SuratMasukKadisDisposisiRequest $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        try {
            $this->workflow->kadisDispose(
                $surat_masuk,
                $request,
                $request->validated('disposisi'),
                $request->input('note'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return redirect()
            ->route('persuratan-masuk.tindak-lanjut-unit')
            ->with('success', 'Disposisi ke Kabid/Unit berhasil dikirim.');
    }

    public function startProcess(Request $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('process', $surat_masuk);

        $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->workflow->startProcess($surat_masuk, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Status diubah ke proses tindak lanjut.');
    }

    public function completeDisposisi(SuratMasukCompleteDisposisiRequest $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $disposisi = $request->disposisi();

        try {
            $this->workflow->completeDisposisi($surat_masuk, $disposisi, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        $surat_masuk->refresh();
        $message = $surat_masuk->status === 'selesai'
            ? 'Semua disposisi selesai. Surat masuk siap diarsipkan.'
            : 'Disposisi Anda telah ditandai selesai. Menunggu disposisi unit lainnya.';

        return back()->with('success', $message);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', SuratMasuk::class);

        $rows = $this->baseListQuery($request)->latest('id_surat_masuk')->get();
        $filename = 'register-surat-masuk-'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Nomor Agenda',
                'Nomor Surat Pengirim',
                'Tanggal Surat',
                'Tanggal Terima',
                'Pengirim',
                'Perihal',
                'Sifat',
                'Status',
                'Progress Disposisi',
            ]);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->nomor_agenda,
                    $row->nomor_surat_pengirim,
                    optional($row->tanggal_surat)->format('Y-m-d'),
                    optional($row->tanggal_terima)->format('Y-m-d'),
                    $row->pengirim,
                    $row->perihal,
                    SuratMasuk::sifatSuratOptions()[$row->sifat_surat] ?? $row->sifat_surat,
                    $row->statusLabel(),
                    $row->disposisiProgress(),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function archive(Request $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('archive', $surat_masuk);

        $request->validate(['note' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->workflow->archive($surat_masuk, $request, $request->input('note'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return redirect()
            ->route('persuratan-masuk.arsip')
            ->with('success', 'Surat masuk diarsipkan.');
    }

    public function cancel(Request $request, SuratMasuk $surat_masuk): RedirectResponse
    {
        $this->authorize('cancel', $surat_masuk);

        $request->validate(['alasan' => ['required', 'string', 'max:2000']]);

        try {
            $this->workflow->cancel($surat_masuk, $request, $request->string('alasan'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['workflow' => $e->getMessage()]);
        }

        return back()->with('success', 'Surat masuk dibatalkan.');
    }

    private function stageListPage(Request $request, string $stage): View
    {
        $config = self::STAGE_CONFIG[$stage];
        $query = $this->baseListQuery($request);

        if ($stage === 'unit') {
            $this->scopeUnitInbox($query, $request->user());
        } elseif (! $request->filled('status')) {
            $query->whereIn('status', $config['statuses']);
        }

        $list = $query->latest('id_surat_masuk')->paginate(10)->withQueryString();

        $listRoute = match ($stage) {
            'sekretariat' => 'persuratan-masuk.proses-sekretariat',
            'kadis' => 'persuratan-masuk.disposisi-kadis',
            'unit' => 'persuratan-masuk.tindak-lanjut-unit',
            default => 'persuratan-masuk.index',
        };

        return $this->listView($request, $list, $config['title'], true, $config['workflow'], $listRoute);
    }

    private function listView(
        Request $request,
        $list,
        string $pageTitle,
        bool $isStagePage,
        ?string $workflowContext,
        string $listRoute,
    ): View {
        return view('admin.Kepegawaian.persuratan.surat_masuk.index', [
            'list' => $list,
            'statusOptions' => SuratMasuk::statusOptions(),
            'pageTitle' => $pageTitle,
            'isStagePage' => $isStagePage,
            'workflowContext' => $workflowContext,
            'listRoute' => $listRoute,
            'inboxStats' => $this->inboxStats($request),
        ]);
    }

    private function baseListQuery(Request $request): Builder
    {
        $query = SuratMasuk::query()
            ->with('createdBy:id,name')
            ->withCount([
                'disposisi as disposisi_total_count',
                'disposisi as disposisi_selesai_count' => fn ($q) => $q->where('status', 'selesai'),
            ]);

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function (Builder $sub) use ($term) {
                $sub->where('nomor_agenda', 'like', '%'.$term.'%')
                    ->orWhere('nomor_surat_pengirim', 'like', '%'.$term.'%')
                    ->orWhere('perihal', 'like', '%'.$term.'%')
                    ->orWhere('pengirim', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query;
    }

    private function resolveWorkflowContext(Request $request): string
    {
        $context = (string) $request->query('context', '');

        if (in_array($context, ['sekretariat', 'kadis', 'unit'], true)) {
            return $context;
        }

        $user = $request->user();
        if ($this->isKadis($request) && ! $this->isSekretariatOperator($request)) {
            return 'kadis';
        }
        if ($this->isKabid($request) && ! $this->isSekretariatOperator($request)) {
            return 'unit';
        }

        return 'sekretariat';
    }

    private function backRouteForContext(string $context): string
    {
        return match ($context) {
            'kadis' => 'persuratan-masuk.disposisi-kadis',
            'unit' => 'persuratan-masuk.tindak-lanjut-unit',
            default => 'persuratan-masuk.proses-sekretariat',
        };
    }

    private function isSekretariatOperator(Request $request): bool
    {
        $user = $request->user();

        return $user && PersuratanMasukPermissions::canSekretariat($user);
    }

    private function isKadis(Request $request): bool
    {
        $user = $request->user();

        return $user && PersuratanMasukPermissions::canKadis($user);
    }

    private function isKabid(Request $request): bool
    {
        $user = $request->user();

        return $user && PersuratanMasukPermissions::canKabid($user);
    }

    /**
     * @return array<string, int>
     */
    private function inboxStats(Request $request): array
    {
        $user = $request->user();

        return [
            'sekretariat' => SuratMasuk::query()->whereIn('status', ['tercatat', 'agenda'])->count(),
            'kadis' => SuratMasuk::query()->where('status', 'menunggu_disposisi_kadis')->count(),
            'unit' => $user ? $this->countUnitInbox($user) : 0,
            'overdue' => SuratMasuk::query()
                ->whereHas('disposisi', fn ($q) => $q->where('status', 'aktif')->whereDate('batas_waktu', '<', now()->toDateString()))
                ->count(),
        ];
    }

    private function countUnitInbox($user): int
    {
        $query = SuratMasuk::query();
        $this->scopeUnitInbox($query, $user);

        return $query->count();
    }

    private function disposisiMatchesUser($user, SuratMasukDisposisi $disposisi): bool
    {
        $idPegawai = (int) ($user->id_pegawai ?? 0);
        if ($idPegawai && (int) $disposisi->to_pegawai_id === $idPegawai) {
            return true;
        }

        $unit = $idPegawai ? $this->resolveUnitKerja($idPegawai) : null;

        return $unit && $disposisi->to_unit_kerja === $unit;
    }

    private function scopeUnitInbox(Builder $query, $user): void
    {
        $idPegawai = (int) ($user?->id_pegawai ?? 0);
        $unit = $idPegawai ? $this->resolveUnitKerja($idPegawai) : null;

        $query->whereIn('status', ['disposisi_ke_unit', 'proses'])
            ->whereHas('disposisi', function (Builder $q) use ($idPegawai, $unit) {
                $q->where('status', 'aktif')
                    ->where(function (Builder $sub) use ($idPegawai, $unit) {
                        if ($idPegawai) {
                            $sub->where('to_pegawai_id', $idPegawai);
                        }
                        if ($unit) {
                            $sub->orWhere('to_unit_kerja', $unit);
                        }
                    });
            });
    }

    private function resolveUnitKerja(int $idPegawai): ?string
    {
        return JabatanPegawai::query()
            ->where('id_pegawai', $idPegawai)
            ->where('status_jabatan', 'Aktif')
            ->orderByDesc('tmt')
            ->value('unit_kerja');
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
            $path = $file->store('surat-masuk/lampiran', 'public');
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
}
