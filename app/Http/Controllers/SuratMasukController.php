<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\View\View;

class SuratMasukController extends Controller
{
    public function index(): View
    {
        $list = SuratMasuk::query()
            ->with('suratKeluar:id_surat_keluar,nomor_surat,perihal')
            ->latest('id_surat_masuk')
            ->paginate(10);

        return view('admin.Kepegawaian.persuratan.surat_masuk.index', compact('list'));
    }
}
