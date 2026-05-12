<?php

namespace App\Http\Controllers;

use App\Models\Karcis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KarcisController extends Controller
{
    public function index(): View
    {
        $list = Karcis::query()
            ->latest('created_at')
            ->paginate(10);

        return view('admin.Retribusi.karcis.index', compact('list'));
    }

    public function create(): View
    {
        return view('admin.Retribusi.karcis.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        Karcis::create($data);

        return redirect()
            ->route('karcis.index')
            ->with('success', 'Data karcis berhasil ditambahkan.');
    }

    public function show(Karcis $karcis): View
    {
        return view('admin.Retribusi.karcis.show', compact('karcis'));
    }

    public function edit(Karcis $karcis): View
    {
        return view('admin.Retribusi.karcis.edit', compact('karcis'));
    }

    public function update(Request $request, Karcis $karcis): RedirectResponse
    {
        $data = $request->validate($this->rules($karcis->kode_karcis));
        $karcis->update($data);

        return redirect()
            ->route('karcis.index')
            ->with('success', 'Data karcis berhasil diperbarui.');
    }

    public function destroy(Karcis $karcis): RedirectResponse
    {
        $karcis->delete();

        return redirect()
            ->route('karcis.index')
            ->with('success', 'Data karcis berhasil dihapus.');
    }

    private function rules(?string $currentKode = null): array
    {
        return [
            'kode_karcis' => [
                'required',
                'string',
                'max:30',
                Rule::unique('karcis', 'kode_karcis')->ignore($currentKode, 'kode_karcis'),
            ],
            'nama_karcis' => ['required', 'string', 'max:255'],
            'harga_satuan' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }
}
