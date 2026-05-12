<?php

namespace App\Http\Controllers;

use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenPegawaiController extends Controller
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
        $riwayatDokumen = $preselectId !== null
            ? DokumenPegawai::query()
                ->where('id_pegawai', $preselectId)
                ->latest('id_dokumen')
                ->get()
            : collect();

        return view('admin.Kepegawaian.dokumen.create', compact('pegawaiOptions', 'preselectId', 'preselectPegawai', 'riwayatDokumen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_pegawai' => ['required', 'exists:pegawai,id_pegawai'],
            'nama_dokumen' => ['required', 'string', 'max:150'],
            'file_dokumen' => ['required', 'file', 'max:5120'],
        ]);

        $data['file_dokumen'] = $request->file('file_dokumen')->store('pegawai/dokumen', 'public');
        DokumenPegawai::create($data);
        $pegawai = Pegawai::findOrFail($data['id_pegawai']);

        return redirect()
            ->route('dokumen-pegawai.create', ['id_pegawai' => $pegawai->id_pegawai])
            ->with('success', 'Dokumen tersimpan. Silakan tambahkan dokumen lainnya atau klik Simpan untuk selesai.');
    }
}
