<?php

namespace App\Http\Controllers;

use App\Models\ArsipSuratKeluar;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArsipSuratKeluarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ArsipSuratKeluar::class);

        $list = ArsipSuratKeluar::query()
            ->with('suratKeluar:id_surat_keluar,nomor_surat,perihal,status')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('nomor_surat', 'like', '%'.$term.'%')
                        ->orWhere('perihal', 'like', '%'.$term.'%')
                        ->orWhere('pengirim', 'like', '%'.$term.'%');
                });
            })
            ->latest('id_surat_masuk')
            ->paginate(10)
            ->withQueryString();

        return view('admin.Kepegawaian.persuratan.arsip.index', compact('list'));
    }
}
