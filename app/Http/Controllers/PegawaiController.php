<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function index(): View
    {
        $list = Pegawai::latest('id_pegawai')->paginate(10);

        return view('admin.Kepegawaian.pegawai.index', compact('list'));
    }

    public function create(): View
    {
        return view('admin.Kepegawaian.pegawai.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('pegawai', 'public');
        }

        $pegawai = Pegawai::create($data);

        return redirect()
            ->route('jabatan-pegawai.create', ['id_pegawai' => $pegawai->id_pegawai])
            ->with('success', 'Data pegawai tersimpan. Lengkapi jabatan untuk pegawai yang sama di bawah ini.');
    }

    public function show(Request $request, Pegawai $pegawai): View
    {
        $pegawai->load([
            'user',
            'jabatanPegawai' => fn ($q) => $q->orderByDesc('tmt')->orderByDesc('id_jabatan'),
            'pendidikanPegawai' => fn ($q) => $q->orderByDesc('tahun_lulus')->orderByDesc('id_pendidikan'),
        ]);

        $activeTab = $request->query('tab');
        if (! in_array($activeTab, ['pegawai', 'jabatan', 'pendidikan'], true)) {
            $activeTab = 'pegawai';
        }

        return view('admin.Kepegawaian.pegawai.show', compact('pegawai', 'activeTab'));
    }

    public function edit(Pegawai $pegawai): View
    {
        return view('admin.Kepegawaian.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $data = $request->validate($this->rules());

        if ($request->hasFile('foto')) {
            if ($pegawai->foto) {
                Storage::disk('public')->delete($pegawai->foto);
            }
            $data['foto'] = $request->file('foto')->store('pegawai', 'public');
        }

        $pegawai->update($data);

        $jabatanTerbaru = $pegawai->jabatanPegawai()
            ->orderByDesc('tmt')
            ->orderByDesc('id_jabatan')
            ->first();

        if ($jabatanTerbaru) {
            return redirect()
                ->route('jabatan-pegawai.edit', $jabatanTerbaru)
                ->with('success', 'Data pegawai diperbarui. Silakan lanjutkan pengeditan jabatan.');
        }

        return redirect()
            ->route('jabatan-pegawai.create', ['id_pegawai' => $pegawai->id_pegawai])
            ->with('success', 'Data pegawai diperbarui. Belum ada data jabatan, silakan tambahkan.');
    }

    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        if ($pegawai->foto) {
            Storage::disk('public')->delete($pegawai->foto);
        }

        $pegawai->delete();

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nip' => ['nullable', 'string', 'max:20'],
            'nik' => ['required', 'string', 'max:16'],
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'gelar_depan' => ['nullable', 'string', 'max:20'],
            'gelar_belakang' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['required', 'string', 'max:50'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama' => ['required', 'string', 'max:20'],
            'status_kepegawaian' => ['required', 'in:PNS,PPPK,Honorer/Kontrak'],
            'alamat_ktp' => ['required', 'string'],
            'no_hp' => ['required', 'string', 'max:15'],
            'email_dinas' => ['nullable', 'email', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
