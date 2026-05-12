<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserAccountRequest;
use App\Http\Requests\UpdateUserAccountRequest;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->with('pegawai')
            ->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhereHas('pegawai', function ($pq) use ($q) {
                        $pq->where('nama_lengkap', 'like', '%'.$q.'%')
                            ->orWhere('nip', 'like', '%'.$q.'%')
                            ->orWhere('nik', 'like', '%'.$q.'%');
                    });
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.pengguna.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $pegawaiSiap = Pegawai::query()
            ->whereDoesntHave('user')
            ->orderBy('nama_lengkap')
            ->get();

        $pegawaiMap = $pegawaiSiap->mapWithKeys(function (Pegawai $p) {
            $nama = trim(implode(' ', array_filter([
                $p->gelar_depan,
                $p->nama_lengkap,
                $p->gelar_belakang,
            ])));

            return [
                (string) $p->id_pegawai => [
                    'nama' => $nama,
                    'email' => $p->email_dinas ?? '',
                ],
            ];
        });

        $prefillId = $request->filled('id_pegawai') ? $request->integer('id_pegawai') : null;
        if ($prefillId && ! $pegawaiSiap->firstWhere('id_pegawai', $prefillId)) {
            $prefillId = null;
        }

        return view('admin.pengguna.create', compact('pegawaiSiap', 'pegawaiMap', 'prefillId'));
    }

    public function store(StoreUserAccountRequest $request): RedirectResponse
    {
        $data = [
            'id_pegawai' => $request->validated('id_pegawai'),
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'is_super_admin' => $request->user()->is_super_admin && $request->boolean('is_super_admin'),
        ];

        $user = User::create($data);

        if (! $user->is_super_admin) {
            $user->syncPermissions($request->input('permissions', []));
        }

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(Request $request, User $pengguna): View
    {
        if (! $request->user()->is_super_admin && $pengguna->is_super_admin) {
            abort(403);
        }

        $pengguna->load(['permissions', 'pegawai']);

        return view('admin.pengguna.edit', ['user' => $pengguna]);
    }

    public function update(UpdateUserAccountRequest $request, User $pengguna): RedirectResponse
    {
        if (! $request->user()->is_super_admin && $pengguna->is_super_admin) {
            abort(403);
        }

        $pengguna->name = $request->validated('name');
        $pengguna->email = $request->validated('email');

        if ($request->filled('password')) {
            $pengguna->password = $request->validated('password');
        }

        if ($request->user()->is_super_admin) {
            $pengguna->is_super_admin = $request->boolean('is_super_admin');
        }

        $pengguna->save();

        if (! $pengguna->is_super_admin) {
            $pengguna->syncPermissions($request->input('permissions', []));
        } else {
            $pengguna->permissions()->delete();
        }

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($request->user()->id === $pengguna->id) {
            return redirect()
                ->route('pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun yang sedang aktif.');
        }

        if ($pengguna->is_super_admin && ! $request->user()->is_super_admin) {
            return redirect()
                ->route('pengguna.index')
                ->with('error', 'Hanya super admin yang dapat menghapus akun super admin.');
        }

        if ($pengguna->is_super_admin) {
            $count = User::query()->where('is_super_admin', true)->count();
            if ($count <= 1) {
                return redirect()
                    ->route('pengguna.index')
                    ->with('error', 'Tidak dapat menghapus satu-satunya super admin.');
            }
        }

        $pengguna->delete();

        return redirect()
            ->route('pengguna.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}
