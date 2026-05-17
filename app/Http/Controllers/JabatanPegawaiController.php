<?php

namespace App\Http\Controllers;

use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JabatanPegawaiController extends Controller
{
    public function create(Request $request): View
    {
        $pegawaiOptions = Pegawai::orderBy('nama_lengkap')
            ->get(['id_pegawai', 'nama_lengkap', 'nip']);

        $q = $request->query('id_pegawai');
        $preselectId = $q !== null && $q !== '' ? (int) $q : null;
        if ($preselectId !== null && ! $pegawaiOptions->contains(fn ($p) => (int) $p->id_pegawai === $preselectId)) {
            $preselectId = null;
        }
        $preselectPegawai = $preselectId !== null
            ? $pegawaiOptions->firstWhere('id_pegawai', $preselectId)
            : null;

        return view('admin.Kepegawaian.pegawai.jabatan.create', compact('pegawaiOptions', 'preselectId', 'preselectPegawai'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        JabatanPegawai::create($data);
        $pegawai = Pegawai::findOrFail($data['id_pegawai']);

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'jabatan'])
            ->with('success', 'Data jabatan berhasil ditambahkan.');
    }

    public function edit(JabatanPegawai $jabatan_pegawai): View
    {
        $jabatan_pegawai->load('pegawai');

        return view('admin.Kepegawaian.pegawai.jabatan.edit', ['jabatanPegawai' => $jabatan_pegawai]);
    }

    public function update(Request $request, JabatanPegawai $jabatan_pegawai): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $jabatan_pegawai->update($data);
        $pegawai = Pegawai::findOrFail($data['id_pegawai']);

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'jabatan'])
            ->with('success', 'Data jabatan berhasil diperbarui.');
    }

    private function rules(): array
    {
        return [
            'id_pegawai' => ['required', 'exists:pegawai,id_pegawai'],
            'instansi' => ['required', 'string', 'max:200'],
            'jabatan' => ['required', 'string', 'max:150'],
            'unit_kerja' => ['required', 'string', 'max:150'],
            'karpeg' => ['nullable', 'string', 'max:100'],
            'pangkat_golongan' => ['nullable', 'string', 'max:100'],
            'masa_kerja_gol_tahun' => ['nullable', 'integer', 'min:0', 'max:99'],
            'masa_kerja_gol_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            'pelatihan_pim_i' => ['nullable', 'string', 'max:100'],
            'pelatihan_pim_ii' => ['nullable', 'string', 'max:100'],
            'pelatihan_pim_iii' => ['nullable', 'string', 'max:100'],
            'pelatihan_pim_iv' => ['nullable', 'string', 'max:100'],
            'jlh_jam' => ['nullable', 'integer', 'min:0'],
            'masa_kerja_sel_tahun' => ['nullable', 'integer', 'min:0', 'max:99'],
            'masa_kerja_sel_bulan' => ['nullable', 'integer', 'min:0', 'max:11'],
            'tmt_berkala_terakhir' => ['nullable', 'date'],
            'tmt_cpnsd' => ['nullable', 'date'],
            'tmt_pns' => ['nullable', 'date'],
            'ket' => ['nullable', 'string', 'max:500'],
            'tmt' => ['required', 'date'],
            'status_jabatan' => ['required', 'in:Aktif,Tidak Aktif'],
        ];
    }
}
