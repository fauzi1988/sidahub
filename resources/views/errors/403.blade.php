@php
    $defaultMsg = 'Anda tidak memiliki izin untuk membuka halaman ini.';
    $raw = isset($exception) ? trim($exception->getMessage()) : '';
    $message = ($raw === '' || $raw === 'Forbidden') ? $defaultMsg : $raw;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Akses ditolak — DISHUB HALTIM</title>
   <link rel="icon" href="{{ asset('back/pluto/images/fevicon.png') }}" type="image/png" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/bootstrap.min.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/style.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/colors.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/theme-green-yellow.css') }}" />
   <style>
      body.error-page {
         min-height: 100vh;
         background: linear-gradient(135deg, #026aa7 0%, #0288d1 45%, #f9a825 100%);
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 24px;
      }
      .error-card {
         max-width: 460px;
         width: 100%;
         border-radius: 12px;
         box-shadow: 0 12px 40px rgba(0,0,0,.18);
      }
      .error-card .card-header {
         background: #026aa7;
         color: #fff;
         border-radius: 12px 12px 0 0 !important;
         padding: 1.25rem 1.5rem;
      }
      .error-code { font-size: 2.5rem; font-weight: 700; line-height: 1; opacity: .95; }
   </style>
</head>
<body class="error-page">
   <div class="card error-card border-0">
      <div class="card-header border-0 d-flex align-items-center justify-content-between">
         <div>
            <div class="error-code">403</div>
            <small class="opacity-75">Akses ditolak</small>
         </div>
         <span class="badge badge-light text-dark">DISHUB HALTIM</span>
      </div>
      <div class="card-body bg-white">
         <p class="mb-4">{{ $message }}</p>
         <div class="d-flex flex-wrap align-items-center">
            <button type="button" class="btn btn-outline-secondary mr-2 mb-2" onclick="history.back()">Kembali</button>
            @auth
               <a href="{{ route('dashboard') }}" class="btn btn-primary mb-2">Ke dashboard</a>
            @else
               <a href="{{ url('/') }}" class="btn btn-outline-primary mr-2 mb-2">Halaman awal</a>
               <a href="{{ route('login') }}" class="btn btn-primary mb-2">Masuk</a>
            @endauth
         </div>
      </div>
   </div>
   <script src="{{ asset('back/pluto/js/jquery.min.js') }}"></script>
   <script src="{{ asset('back/pluto/js/bootstrap.min.js') }}"></script>
</body>
</html>
