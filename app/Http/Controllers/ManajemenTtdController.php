<?php

namespace App\Http\Controllers;

use App\Models\ManajemenTtd;
use App\Models\SuratKeluar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManajemenTtdController extends Controller
{
    public function index(Request $request): View
    {
        $query = ManajemenTtd::query()->latest('id_ttd');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_ttd', 'like', '%'.$q.'%')
                    ->orWhere('pemilik_ttd', 'like', '%'.$q.'%')
                    ->orWhere('jabatan_pemilik', 'like', '%'.$q.'%');
            });
        }

        if ($request->filled('jenis_ttd')) {
            $query->where('jenis_ttd', $request->string('jenis_ttd'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        $list = $query->paginate(10)->withQueryString();
        $jenisOptions = SuratKeluar::jenisTtdOptions();

        return view('admin.Kepegawaian.persuratan.manajemen_ttd.index', compact('list', 'jenisOptions'));
    }

    public function create(): View
    {
        $jenisOptions = SuratKeluar::jenisTtdOptions();

        return view('admin.Kepegawaian.persuratan.manajemen_ttd.create', compact('jenisOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('file_ttd')) {
            $data['file_ttd'] = $request->file('file_ttd')->store('ttd', 'public');
        }

        ManajemenTtd::create($data);

        return redirect()->route('manajemen-ttd.index')
            ->with('success', 'Master TTD berhasil ditambahkan.');
    }

    public function show(ManajemenTtd $manajemen_ttd): View
    {
        return view('admin.Kepegawaian.persuratan.manajemen_ttd.show', compact('manajemen_ttd'));
    }

    public function edit(ManajemenTtd $manajemen_ttd): View
    {
        $jenisOptions = SuratKeluar::jenisTtdOptions();

        return view('admin.Kepegawaian.persuratan.manajemen_ttd.edit', compact('manajemen_ttd', 'jenisOptions'));
    }

    public function update(Request $request, ManajemenTtd $manajemen_ttd): RedirectResponse
    {
        $data = $request->validate($this->rules($manajemen_ttd));
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('file_ttd')) {
            if ($manajemen_ttd->file_ttd) {
                Storage::disk('public')->delete($manajemen_ttd->file_ttd);
            }
            $data['file_ttd'] = $request->file('file_ttd')->store('ttd', 'public');
        }

        $manajemen_ttd->update($data);

        return redirect()->route('manajemen-ttd.index')
            ->with('success', 'Master TTD berhasil diperbarui.');
    }

    public function destroy(ManajemenTtd $manajemen_ttd): RedirectResponse
    {
        if ($manajemen_ttd->file_ttd) {
            Storage::disk('public')->delete($manajemen_ttd->file_ttd);
        }
        $manajemen_ttd->delete();

        return redirect()->route('manajemen-ttd.index')
            ->with('success', 'Master TTD berhasil dihapus.');
    }

    private function rules(?ManajemenTtd $ttd = null): array
    {
        $uniqueName = Rule::unique('manajemen_ttd', 'nama_ttd');
        if ($ttd) {
            $uniqueName = $uniqueName->ignore($ttd->id_ttd, 'id_ttd');
        }

        return [
            'nama_ttd' => ['required', 'string', 'max:150', $uniqueName],
            'jenis_ttd' => ['required', Rule::in(array_keys(SuratKeluar::jenisTtdOptions()))],
            'pemilik_ttd' => ['nullable', 'string', 'max:150'],
            'jabatan_pemilik' => ['nullable', 'string', 'max:150'],
            'file_ttd' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:2048'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

