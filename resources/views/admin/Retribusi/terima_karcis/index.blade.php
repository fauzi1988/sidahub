@extends('layouts.main')
@section('container')
<div class="row">
   <div class="col-12">
      <div class="page_title mb-4 d-flex justify-content-between align-items-center flex-wrap">
         <h2 class="mb-0">Penerimaan Karcis</h2>
         <div class="btn-actions">
            <a href="{{ route('terima-karcis.create') }}" class="btn btn-primary">Tambah Penerimaan</a>
         </div>
      </div>
   </div>
</div>

@if(session('success'))
   <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
   </div>
@endif

<div class="white_shd full margin_bottom_30">
   <div class="full graph_revenue p-4">
      <form action="{{ route('terima-karcis.index') }}" method="GET" class="mb-4" id="filter-form">
         <div class="form-row align-items-end">
            <div class="form-group col-md-3">
               <label>Filter Berdasarkan</label>
               <select name="filter_by" id="filter-by" class="form-control">
                  <option value="nomor_bast" @selected(($filterBy ?? 'nomor_bast') === 'nomor_bast')>Nomor BAST</option>
                  <option value="nama_karcis" @selected(($filterBy ?? 'nomor_bast') === 'nama_karcis')>Nama Karcis</option>
                  <option value="tahun" @selected(($filterBy ?? 'nomor_bast') === 'tahun')>Tahun</option>
               </select>
            </div>
            <div class="form-group col-md-6">
               <label>Kata Kunci</label>
               <input type="text" name="keyword" id="filter-keyword" class="form-control" value="{{ $keyword ?? '' }}" placeholder="Masukkan kata kunci pencarian...">
            </div>
            <div class="form-group col-md-3">
               <button type="submit" class="btn btn-primary mr-2">Cari</button>
               <a href="{{ route('terima-karcis.index') }}" class="btn btn-secondary">Reset</a>
            </div>
         </div>
      </form>
      <script>
         (function () {
            const form = document.getElementById('filter-form');
            const filterByEl = document.getElementById('filter-by');
            const keywordEl = document.getElementById('filter-keyword');
            if (!form || !filterByEl || !keywordEl) return;

            const updatePlaceholder = () => {
               if (filterByEl.value === 'tahun') {
                  keywordEl.placeholder = 'Ketik tahun, contoh: 2026';
                  return;
               }
               keywordEl.placeholder = 'Masukkan kata kunci pencarian...';
            };

            let typingTimer = null;
            keywordEl.addEventListener('input', () => {
               if (filterByEl.value !== 'tahun') return;
               clearTimeout(typingTimer);
               typingTimer = setTimeout(() => {
                  form.submit();
               }, 350);
            });

            filterByEl.addEventListener('change', updatePlaceholder);
            updatePlaceholder();
         })();
      </script>

      @if($list->isEmpty())
         <p class="text-muted mb-0">Belum ada data penerimaan karcis.</p>
      @else
         <div class="table-responsive">
            <table class="table table-bordered table-hover">
               <thead class="thead-light">
                  <tr>
                     <th>No</th>
                     <th>Tanggal</th>
                     <th>Nomor BAST</th>
                     <th>Nama Karcis</th>
                     <th>Harga Satuan</th>
                     <th>Stock Masuk</th>
                     <th>Total Stock</th>
                     <th>Total Harga</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($list as $row)
                  <tr>
                     <td>{{ $list->firstItem() + $loop->index }}</td>
                     <td>{{ $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-' }}</td>
                     <td>{{ $row->nomor_bast }}</td>
                     <td>{{ $row->karcis->nama_karcis }}</td>
                     <td>Rp {{ number_format((float) $row->harga_satuan, 0, ',', '.') }}</td>
                     <td>{{ number_format((int) $row->stock_masuk, 0, ',', '.') }}</td>
                     <td>{{ number_format((int) $row->total_stock, 0, ',', '.') }}</td>
                     <td>Rp {{ number_format((float) $row->total_harga, 0, ',', '.') }}</td>
                     <td>
                        <div class="btn-actions btn-actions--compact">
                           <a href="{{ route('terima-karcis.show', $row) }}" class="btn btn-sm btn-secondary">Detail</a>
                           <a href="{{ route('terima-karcis.edit', $row) }}" class="btn btn-sm btn-warning">Edit</a>
                           <form action="{{ route('terima-karcis.destroy', $row) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                           </form>
                        </div>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
         <div class="mt-3">{{ $list->links() }}</div>
      @endif
   </div>
</div>
@endsection
