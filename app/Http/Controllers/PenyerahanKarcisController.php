<?php

namespace App\Http\Controllers;

use App\Models\Karcis;
use App\Models\Pegawai;
use App\Models\PenyerahanKarcis;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PenyerahanKarcisController extends Controller
{
    public function index(): View
    {
        $list = PenyerahanKarcis::query()
            ->latest('id')
            ->paginate(10);

        return view('admin.Retribusi.penyerahan_karcis.index', compact('list'));
    }

    public function create(): View
    {
        $karcisOptions = Karcis::query()
            ->orderBy('nama_karcis')
            ->get(['kode_karcis', 'nama_karcis', 'harga_satuan']);
        $pegawaiOptions = $this->pegawaiForSelect();
        $pegawaiPayload = $this->pegawaiPayload($pegawaiOptions);
        $pegawaiSearchList = $this->pegawaiSearchList($pegawaiOptions);

        return view('admin.Retribusi.penyerahan_karcis.create', compact('karcisOptions', 'pegawaiOptions', 'pegawaiPayload', 'pegawaiSearchList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $p1 = Pegawai::with('jabatanPegawai')->findOrFail($data['pihak_pertama_id_pegawai']);
        $p2 = Pegawai::with('jabatanPegawai')->findOrFail($data['pihak_kedua_id_pegawai']);
        $s1 = $this->snapshotPihak($p1);
        $s2 = $this->snapshotPihak($p2);

        DB::transaction(function () use ($data, $p1, $p2, $s1, $s2): void {
            $penyerahan = PenyerahanKarcis::create([
                'nomor_bast' => $data['nomor_bast'],
                'tanggal' => $data['tanggal'],
                'pihak_pertama_id_pegawai' => $p1->id_pegawai,
                'pihak_kedua_id_pegawai' => $p2->id_pegawai,
                'pihak_pertama_nama' => $s1['nama'],
                'pihak_pertama_nip' => $s1['nip'],
                'pihak_pertama_jabatan' => $s1['jabatan'],
                'pihak_pertama_instansi' => $s1['instansi'],
                'pihak_pertama_alamat' => $s1['alamat'],
                'pihak_kedua_nama' => $s2['nama'],
                'pihak_kedua_nip' => $s2['nip'],
                'pihak_kedua_jabatan' => $s2['jabatan'],
                'pihak_kedua_tempat_tugas' => $s2['tempat_tugas'],
                'pihak_kedua_instansi' => $s2['instansi'],
                'pihak_kedua_alamat' => $s2['alamat'],
                'mengetahui_nama' => $data['mengetahui_nama'] ?? null,
                'mengetahui_nip' => $data['mengetahui_nip'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $karcis = Karcis::query()->findOrFail($item['karcis_kode']);
                $lembar = (int) $item['lembar'];
                $hargaSatuan = (float) $karcis->harga_satuan;

                $penyerahan->items()->create([
                    'karcis_kode' => $karcis->kode_karcis,
                    'harga_satuan' => $hargaSatuan,
                    'lembar' => $lembar,
                    'total' => $hargaSatuan * $lembar,
                    'uraian' => $karcis->nama_karcis,
                    'banyak_buku' => $lembar,
                    'tarif' => $hargaSatuan,
                    'nomor_seri_awal' => $item['nomor_seri_awal'],
                    'nomor_seri_akhir' => $item['nomor_seri_akhir'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('penyerahan-karcis.index')
            ->with('success', 'Data penyerahan karcis berhasil ditambahkan.');
    }

    public function show(PenyerahanKarcis $penyerahan_karci): View
    {
        $penyerahan_karci->load('items.karcis', 'pihakPertamaPegawai', 'pihakKeduaPegawai');

        return view('admin.Retribusi.penyerahan_karcis.show', ['penyerahan' => $penyerahan_karci]);
    }

    public function print(PenyerahanKarcis $penyerahan_karci)
    {
        $penyerahan_karci->load('items.karcis');

        $pdf = Pdf::loadView('admin.Retribusi.penyerahan_karcis.print', [
            'penyerahan' => $penyerahan_karci,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('penyerahan-karcis-'.$penyerahan_karci->id.'.pdf');
    }

    public function edit(PenyerahanKarcis $penyerahan_karci): View
    {
        $penyerahan_karci->load('items.karcis');
        $karcisOptions = Karcis::query()
            ->orderBy('nama_karcis')
            ->get(['kode_karcis', 'nama_karcis', 'harga_satuan']);
        $pegawaiOptions = $this->pegawaiForSelect();
        $pegawaiPayload = $this->pegawaiPayload($pegawaiOptions);
        $pegawaiSearchList = $this->pegawaiSearchList($pegawaiOptions);

        return view('admin.Retribusi.penyerahan_karcis.edit', [
            'penyerahan' => $penyerahan_karci,
            'karcisOptions' => $karcisOptions,
            'pegawaiOptions' => $pegawaiOptions,
            'pegawaiPayload' => $pegawaiPayload,
            'pegawaiSearchList' => $pegawaiSearchList,
        ]);
    }

    public function update(Request $request, PenyerahanKarcis $penyerahan_karci): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $p1 = Pegawai::with('jabatanPegawai')->findOrFail($data['pihak_pertama_id_pegawai']);
        $p2 = Pegawai::with('jabatanPegawai')->findOrFail($data['pihak_kedua_id_pegawai']);
        $s1 = $this->snapshotPihak($p1);
        $s2 = $this->snapshotPihak($p2);

        DB::transaction(function () use ($data, $penyerahan_karci, $p1, $p2, $s1, $s2): void {
            $payload = [
                'nomor_bast' => $data['nomor_bast'],
                'tanggal' => $data['tanggal'],
                'pihak_pertama_id_pegawai' => $p1->id_pegawai,
                'pihak_kedua_id_pegawai' => $p2->id_pegawai,
                'pihak_pertama_nama' => $s1['nama'],
                'pihak_pertama_nip' => $s1['nip'],
                'pihak_pertama_jabatan' => $s1['jabatan'],
                'pihak_pertama_instansi' => $s1['instansi'],
                'pihak_pertama_alamat' => $s1['alamat'],
                'pihak_kedua_nama' => $s2['nama'],
                'pihak_kedua_nip' => $s2['nip'],
                'pihak_kedua_jabatan' => $s2['jabatan'],
                'pihak_kedua_tempat_tugas' => $s2['tempat_tugas'],
                'pihak_kedua_instansi' => $s2['instansi'],
                'pihak_kedua_alamat' => $s2['alamat'],
                'mengetahui_nama' => $data['mengetahui_nama'] ?? null,
                'mengetahui_nip' => $data['mengetahui_nip'] ?? null,
            ];

            $penyerahan_karci->update($payload);
            $penyerahan_karci->items()->delete();
            foreach ($data['items'] as $item) {
                $karcis = Karcis::query()->findOrFail($item['karcis_kode']);
                $lembar = (int) $item['lembar'];
                $hargaSatuan = (float) $karcis->harga_satuan;

                $penyerahan_karci->items()->create([
                    'karcis_kode' => $karcis->kode_karcis,
                    'harga_satuan' => $hargaSatuan,
                    'lembar' => $lembar,
                    'total' => $hargaSatuan * $lembar,
                    'uraian' => $karcis->nama_karcis,
                    'banyak_buku' => $lembar,
                    'tarif' => $hargaSatuan,
                    'nomor_seri_awal' => $item['nomor_seri_awal'],
                    'nomor_seri_akhir' => $item['nomor_seri_akhir'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('penyerahan-karcis.index')
            ->with('success', 'Data penyerahan karcis berhasil diperbarui.');
    }

    public function destroy(PenyerahanKarcis $penyerahan_karci): RedirectResponse
    {
        if ($penyerahan_karci->file_surat) {
            Storage::disk('public')->delete($penyerahan_karci->file_surat);
        }
        $penyerahan_karci->delete();

        return redirect()
            ->route('penyerahan-karcis.index')
            ->with('success', 'Data penyerahan karcis berhasil dihapus.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Pegawai>
     */
    private function pegawaiForSelect()
    {
        return Pegawai::query()
            ->with(['jabatanPegawai' => fn ($q) => $q->orderByDesc('tmt')->orderByDesc('id_jabatan')])
            ->orderBy('nama_lengkap')
            ->get();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Pegawai>  $pegawaiOptions
     * @return list<array{id: int, nip: string, nama: string, label: string}>
     */
    private function pegawaiSearchList($pegawaiOptions): array
    {
        return $pegawaiOptions->map(function (Pegawai $p) {
            $nama = trim(implode(' ', array_filter([
                $p->gelar_depan,
                $p->nama_lengkap,
                $p->gelar_belakang,
            ])));
            $nip = $p->nip ?? '';

            return [
                'id' => (int) $p->id_pegawai,
                'nip' => $nip,
                'nama' => $nama,
                'label' => ($nip !== '' ? $nip : 'Tanpa NIP').' — '.$nama,
            ];
        })->values()->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Pegawai>  $pegawaiOptions
     * @return array<string, array{nama: string, nip: string|null, jabatan: string|null, instansi: string, alamat: string|null, tempat_tugas: string|null}>
     */
    private function pegawaiPayload($pegawaiOptions): array
    {
        $out = [];
        foreach ($pegawaiOptions as $p) {
            $out[(string) $p->id_pegawai] = $this->snapshotPihak($p);
        }

        return $out;
    }

    /**
     * @return array{nama: string, nip: string|null, jabatan: string|null, instansi: string, alamat: string|null, tempat_tugas: string|null}
     */
    private function snapshotPihak(Pegawai $pegawai): array
    {
        $rows = $pegawai->relationLoaded('jabatanPegawai')
            ? $pegawai->jabatanPegawai
            : $pegawai->jabatanPegawai()->orderByDesc('tmt')->orderByDesc('id_jabatan')->get();

        $jabatan = $rows->where('status_jabatan', 'Aktif')
            ->sortByDesc(fn ($j) => $j->tmt?->getTimestamp() ?? 0)
            ->first()
            ?? $rows->sortByDesc(fn ($j) => $j->tmt?->getTimestamp() ?? 0)
                ->first();

        $nama = trim(implode(' ', array_filter([
            $pegawai->gelar_depan,
            $pegawai->nama_lengkap,
            $pegawai->gelar_belakang,
        ])));

        return [
            'nama' => $nama,
            'nip' => $pegawai->nip,
            'jabatan' => $jabatan?->jabatan,
            'instansi' => $jabatan?->instansi ?? 'Dinas Perhubungan',
            'alamat' => $pegawai->alamat_ktp,
            'tempat_tugas' => $jabatan?->unit_kerja,
        ];
    }

    private function rules(): array
    {
        return [
            'nomor_bast' => ['required', 'string', 'max:120'],
            'tanggal' => ['required', 'date'],
            'pihak_pertama_id_pegawai' => ['required', 'integer', 'exists:pegawai,id_pegawai'],
            'pihak_kedua_id_pegawai' => ['required', 'integer', 'exists:pegawai,id_pegawai', 'different:pihak_pertama_id_pegawai'],
            'mengetahui_nama' => ['nullable', 'string', 'max:150'],
            'mengetahui_nip' => ['nullable', 'string', 'max:30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.karcis_kode' => ['required', 'exists:karcis,kode_karcis'],
            'items.*.lembar' => ['required', 'integer', 'min:1'],
            'items.*.nomor_seri_awal' => ['required', 'string', 'max:30'],
            'items.*.nomor_seri_akhir' => ['required', 'string', 'max:30'],
            'items.*.keterangan' => ['nullable', 'string', 'max:150'],
        ];
    }
}
