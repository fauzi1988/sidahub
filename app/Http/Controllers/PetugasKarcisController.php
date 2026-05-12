<?php

namespace App\Http\Controllers;

use App\Models\PetugasKarcis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PetugasKarcisController extends Controller
{
    public function index(): View
    {
        $list = PetugasKarcis::query()
            ->latest('id')
            ->paginate(10);

        return view('admin.Retribusi.petugas_karcis.index', compact('list'));
    }

    public function create(): View
    {
        return view('admin.Retribusi.petugas_karcis.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['foto'] = $request->hasFile('foto')
            ? $request->file('foto')->store('petugas-karcis', 'public')
            : null;

        PetugasKarcis::create($data);

        return redirect()
            ->route('petugas-karcis.index')
            ->with('success', 'Data petugas karcis berhasil ditambahkan.');
    }

    public function show(PetugasKarcis $petugas_karci): View
    {
        return view('admin.Retribusi.petugas_karcis.show', ['petugas' => $petugas_karci]);
    }

    public function edit(PetugasKarcis $petugas_karci): View
    {
        return view('admin.Retribusi.petugas_karcis.edit', ['petugas' => $petugas_karci]);
    }

    public function update(Request $request, PetugasKarcis $petugas_karci): RedirectResponse
    {
        $data = $request->validate($this->rules((int) $petugas_karci->id));

        if ($request->hasFile('foto')) {
            if ($petugas_karci->foto) {
                Storage::disk('public')->delete($petugas_karci->foto);
            }

            $data['foto'] = $request->file('foto')->store('petugas-karcis', 'public');
        }

        $petugas_karci->update($data);

        return redirect()
            ->route('petugas-karcis.index')
            ->with('success', 'Data petugas karcis berhasil diperbarui.');
    }

    public function destroy(PetugasKarcis $petugas_karci): RedirectResponse
    {
        if ($petugas_karci->foto) {
            Storage::disk('public')->delete($petugas_karci->foto);
        }

        $petugas_karci->delete();

        return redirect()
            ->route('petugas-karcis.index')
            ->with('success', 'Data petugas karcis berhasil dihapus.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'nomor_induk_pegawai' => [
                'required',
                'string',
                'max:50',
                Rule::unique('petugas_karcis', 'nomor_induk_pegawai')->ignore($id),
            ],
            'nama_pegawai' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:PNS,PPPK,Tenaga Kontrak'],
            'instansi' => ['required', 'string', 'max:200'],
            'tempat_tugas' => ['required', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
