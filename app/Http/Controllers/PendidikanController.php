<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PendidikanPegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendidikanController extends Controller
{
    public function index(): View
    {
        $list = PendidikanPegawai::query()
            ->with('pegawai:id_pegawai,nama_lengkap,nip')
            ->latest('id_pendidikan')
            ->paginate(10);

        return view('admin.Kepegawaian.pendidikan.index', compact('list'));
    }

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
        $riwayatPendidikan = $preselectId !== null
            ? PendidikanPegawai::query()
                ->where('id_pegawai', $preselectId)
                ->latest('id_pendidikan')
                ->get()
            : collect();

        return view('admin.Kepegawaian.pendidikan.create', compact('pegawaiOptions', 'preselectId', 'preselectPegawai', 'riwayatPendidikan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        PendidikanPegawai::create($data);
        $pegawai = Pegawai::findOrFail($data['id_pegawai']);

        $redirectToEdit = $request->boolean('from_pegawai_edit');

        return redirect()
            ->route($redirectToEdit ? 'pegawai.edit' : 'pendidikan.create', $redirectToEdit
                ? ['pegawai' => $pegawai, 'tab' => 'pendidikan']
                : ['id_pegawai' => $pegawai->id_pegawai])
            ->with('success', $redirectToEdit
                ? 'Data pendidikan berhasil ditambahkan.'
                : 'Data pendidikan tersimpan. Silakan tambahkan data pendidikan lainnya atau klik Simpan untuk lanjut.');
    }

    public function show(PendidikanPegawai $pendidikan_pegawai): View
    {
        $pendidikan_pegawai->load('pegawai');

        return view('admin.Kepegawaian.pendidikan.show', ['pendidikan' => $pendidikan_pegawai]);
    }

    public function edit(PendidikanPegawai $pendidikan_pegawai): View
    {
        $pendidikan_pegawai->load('pegawai');

        return view('admin.Kepegawaian.pendidikan.edit', ['pendidikan' => $pendidikan_pegawai]);
    }

    public function update(Request $request, PendidikanPegawai $pendidikan_pegawai): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $pendidikan_pegawai->update($data);
        $pegawai = Pegawai::findOrFail($data['id_pegawai']);

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'pendidikan'])
            ->with('success', 'Data pendidikan berhasil diperbarui.');
    }

    public function destroy(PendidikanPegawai $pendidikan_pegawai): RedirectResponse
    {
        $pegawai = Pegawai::findOrFail($pendidikan_pegawai->id_pegawai);
        $pendidikan_pegawai->delete();

        return redirect()
            ->route('pegawai.edit', ['pegawai' => $pegawai, 'tab' => 'pendidikan'])
            ->with('success', 'Data pendidikan berhasil dihapus.');
    }

    private function rules(): array
    {
        $yearMax = (int) date('Y') + 1;

        return [
            'id_pegawai' => ['required', 'exists:pegawai,id_pegawai'],
            'tingkat' => ['required', 'in:S1,S2,S3,D3'],
            'jurusan' => ['required', 'string', 'max:150'],
            'nama_institusi' => ['required', 'string', 'max:200'],
            'tahun_lulus' => ['required', 'integer', 'min:1950', 'max:'.$yearMax],
        ];
    }
}
