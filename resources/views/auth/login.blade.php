<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Masuk — DISHUB HALTIM</title>
   <link rel="icon" href="{{ asset('back/pluto/images/fevicon.png') }}" type="image/png" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/bootstrap.min.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/style.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/colors.css') }}" />
   <link rel="stylesheet" href="{{ asset('back/pluto/css/theme-green-yellow.css') }}" />
   <style>
      body.login-page {
         min-height: 100vh;
         background: linear-gradient(135deg, #026aa7 0%, #0288d1 45%, #f9a825 100%);
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 24px;
      }
      .login-card {
         max-width: 420px;
         width: 100%;
         border-radius: 12px;
         box-shadow: 0 12px 40px rgba(0,0,0,.18);
      }
      .login-card .card-header {
         background: #026aa7;
         color: #fff;
         border-radius: 12px 12px 0 0 !important;
         padding: 1.25rem 1.5rem;
      }
      .login-card .card-body { padding: 1.75rem; }
   </style>
</head>
<body class="login-page">
   <div class="card login-card border-0">
      <div class="card-header border-0">
         <h5 class="mb-0 font-weight-bold">DISHUB HALTIM</h5>
         <small class="opacity-75">Silakan masuk untuk melanjutkan</small>
      </div>
      <div class="card-body bg-white">
         @if ($errors->any())
            <div class="alert alert-danger mb-3">
               <ul class="mb-0 pl-3">
                  @foreach ($errors->all() as $error)
                     <li>{{ $error }}</li>
                  @endforeach
               </ul>
            </div>
         @endif
         <form method="post" action="{{ route('login') }}" autocomplete="off">
            @csrf
            <div class="form-group">
               <label for="email">Email</label>
               <input type="email" name="email" id="email" class="form-control"
                      value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
               <label for="password">Kata sandi</label>
               <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group form-check">
               <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1"
                      {{ old('remember') ? 'checked' : '' }}>
               <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
         </form>
      </div>
   </div>
   <script src="{{ asset('back/pluto/js/jquery.min.js') }}"></script>
   <script src="{{ asset('back/pluto/js/bootstrap.min.js') }}"></script>
</body>
</html>
