<?php

namespace App\Http\Controllers;

use App\Models\DokumenPegawai;
use App\Models\Pegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $redirectToEdit = $request->boolean('from_pegawai_edit');

        return redirect()
            ->route($redirectToEdit ? 'pegawai.edit' : 'dokumen-pegawai.create', $redirectToEdit
                ? ['pegawai' => $pegawai, 'tab' => 'dokumen']
                : ['id_pegawai' => $pegawai->id_pegawai])
            ->with('success', $redirectToEdit
                ? 'Dokumen berhasil diunggah.'
                : 'Dokumen tersimpan. Silakan tambahkan dokumen lainnya atau klik Simpan untuk selesai.');
    }

    public function edit(DokumenPegawai $dokumen_pegawai): View
    {
        $dokumen_pegawai->load('pegawai');

        return view('admin.Kepegawaian.dokumen.edit', ['dokumen' => $dokumen_pegawai]);
    }

    public function update(Request $request, DokumenPegawai $dokumen_pegawai): RedirectResponse
    {
        $data = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:150'],
            'file_dokumen' => ['nullable', 'file', 'max:5120'],
        ]);

        if ($request->hasFile('file_dokumen')) {
            if ($dokumen_pegawai->file_dokumen) {
                Storage::disk('public')->delete($dokumen_pegawai->file_dokumen);
            }
            $data['file_dokumen'] = $request->file('file_dokumen')->store('pegawai/dokumen', 'public');
        } else {
            unset($data['file_dokumen']);
        }

        $dokumen_pegawai->update($data);

        $pegawai = Pegawai::findOrFail($dokumen_pegawai->id_pegawai);

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'dokumen'])
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(DokumenPegawai $dokumen_pegawai): RedirectResponse
    {
        $idPegawai = $dokumen_pegawai->id_pegawai;

        if ($dokumen_pegawai->file_dokumen) {
            Storage::disk('public')->delete($dokumen_pegawai->file_dokumen);
        }

        $dokumen_pegawai->delete();

        $pegawai = Pegawai::findOrFail($idPegawai);

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'dokumen'])
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
