<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>DISHUB HALTIM</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- site icon -->
      <link rel="icon" href="{{ asset('back/pluto/images/fevicon.png') }}" type="image/png" />
      <!-- bootstrap css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/bootstrap.min.css') }}" />
      <!-- site css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/style.css') }}" />
      <!-- responsive css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/responsive.css') }}" />
      <!-- color css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/colors.css') }}" />
      <!-- select bootstrap -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/bootstrap-select.css') }}" />
      <!-- scrollbar css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/perfect-scrollbar.css') }}" />
      <!-- custom css -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/custom.css') }}" />
      <!-- theme hijau-kuning -->
      <link rel="stylesheet" href="{{ asset('back/pluto/css/theme-green-yellow.css') }}" />
      @stack('styles')
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
   </head>
   <body class="dashboard dashboard_1">
      @php
         $loggedUser = auth()->user();
         $loggedPegawaiPhoto = $loggedUser?->pegawai?->foto;
         $defaultAvatar = asset('back/pluto/images/layout_img/user_img.jpg');
         $avatarUrl = $loggedPegawaiPhoto ? asset('storage/'.$loggedPegawaiPhoto) : $defaultAvatar;
      @endphp
      <div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            <nav id="sidebar">
               <div class="sidebar_blog_1">
                  <div class="sidebar-header">
                     <div class="logo_section">
                        <a href="{{ route('dashboard') }}"><img class="logo_icon img-responsive" src="{{ asset('back/pluto/images/logo/logo_iconOLD.png') }}" alt="#" /></a>
                     </div>
                  </div>
                  <div class="sidebar_user_info">
                     <div class="icon_setting"></div>
                     <div class="user_profle_side">
                        <div class="user_img"><img class="img-responsive" src="{{ $avatarUrl }}" alt="Foto Profil" /></div>
                        <div class="user_info">
                           <h6>@auth{{ auth()->user()->name }}@else—@endauth</h6>
                           <p><span class="online_animation"></span> Online</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sidebar_blog_2">
                  <h4>Menu</h4>
                  @include('layouts.partials.sidebar-nav')
               </div>
            </nav>
            <!-- end sidebar -->
            <!-- right content -->
            <div id="content">
               <!-- topbar -->
               <div class="topbar">
                  <nav class="navbar navbar-expand-lg navbar-light">
                     <div class="full">
                        <button type="button" id="sidebarCollapse" class="sidebar_toggle"><i class="fa fa-bars"></i></button>
                        <div class="logo_section">
                           <a href="{{ route('dashboard') }}"><img class="img-responsive" src="{{ asset('back/pluto/images/logo/logo.png') }}" alt="#" /></a>
                        </div>
                        <div class="right_topbar">
                           <div class="icon_info">
                              <ul>
                                 @auth
                                 @php
                                    $unreadNotifications = auth()->user()->unreadNotifications()->limit(12)->get();
                                    $unreadCount = auth()->user()->unreadNotifications()->count();
                                 @endphp
                                 <li class="dropdown notification-dropdown-wrap">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Notifikasi">
                                       <i class="fa fa-bell-o"></i>
                                       @if($unreadCount > 0)
                                          <span class="badge badge-danger">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                       @endif
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right notification-dropdown shadow">
                                       <div class="notification-dropdown__header d-flex justify-content-between align-items-center">
                                          <strong>Notifikasi</strong>
                                          @if($unreadCount > 0)
                                             <form method="POST" action="{{ route('notifications.read-all') }}" class="mb-0">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm p-0">Tandai semua dibaca</button>
                                             </form>
                                          @endif
                                       </div>
                                       @forelse($unreadNotifications as $notification)
                                          @php $data = $notification->data; @endphp
                                          <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="notification-dropdown__item mb-0">
                                             @csrf
                                             <button type="submit" class="dropdown-item notification-dropdown__link">
                                                <span class="notification-dropdown__title">{{ $data['message'] ?? 'Pembaruan surat' }}</span>
                                                @if(!empty($data['nomor_agenda']))
                                                   <span class="notification-dropdown__meta">Agenda {{ $data['nomor_agenda'] }}</span>
                                                @endif
                                                @if(!empty($data['status_label']))
                                                   <span class="notification-dropdown__meta">{{ $data['status_label'] }}</span>
                                                @endif
                                                <span class="notification-dropdown__time">{{ $notification->created_at?->diffForHumans() }}</span>
                                             </button>
                                          </form>
                                       @empty
                                          <span class="dropdown-item text-muted small">Tidak ada notifikasi baru.</span>
                                       @endforelse
                                    </div>
                                 </li>
                                 @endauth
                              </ul>
                              <ul class="user_profile_dd">
                                 <li>
                                    <a class="dropdown-toggle" data-toggle="dropdown"><img class="img-responsive rounded-circle" src="{{ $avatarUrl }}" alt="Foto Profil" /><span class="name_user">@auth{{ auth()->user()->name }}@endauth</span></a>
                                    <div class="dropdown-menu">
                                       <a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a>
                                       <form method="post" action="{{ route('logout') }}" class="mb-0">
                                          @csrf
                                          <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-left"><span>Keluar</span> <i class="fa fa-sign-out"></i></button>
                                       </form>
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </nav>
               </div>
               <!-- end topbar -->
               <!-- dashboard inner -->
               <div class="midde_cont">
                  <div class="container-fluid">
                     <div class="row column_title">
                        <div class="col-md-12">
                           <div class="page_title">
                              <h2>DISHUB HALTIM</h2>
                           </div>
                        </div>
                     </div>
                     <div>
                        @yield('container')
                     </div>
                    
                  </div>
                  <!-- footer -->
                  <div class="container-fluid">
                     <div class="footer">
                        <p>Copyright © 2026 by ozi_fauzi. All rights reserved.<br><br>
                           Distributed By: <a href="https://themewagon.com/">ThemeWagon</a>
                        </p>
                     </div>
                  </div>
               </div>
               <!-- end dashboard inner -->
            </div>
         </div>
      </div>
      <!-- jQuery -->
      <script src="{{ asset('back/pluto/js/jquery.min.js') }}"></script>
      <script src="{{ asset('back/pluto/js/popper.min.js') }}"></script>
      <script src="{{ asset('back/pluto/js/bootstrap.min.js') }}"></script>
      <!-- wow animation -->
      <script src="{{ asset('back/pluto/js/animate.js') }}"></script>
      <!-- select country -->
      <script src="{{ asset('back/pluto/js/bootstrap-select.js') }}"></script>
      <!-- owl carousel -->
      <script src="{{ asset('back/pluto/js/owl.carousel.js') }}"></script>
      <!-- chart js -->
      <script src="{{ asset('back/pluto/js/Chart.min.js') }}"></script>
      <script src="{{ asset('back/pluto/js/Chart.bundle.min.js') }}"></script>
      <script src="{{ asset('back/pluto/js/utils.js') }}"></script>
      <script src="{{ asset('back/pluto/js/analyser.js') }}"></script>
      <!-- nice scrollbar -->
      <script src="{{ asset('back/pluto/js/perfect-scrollbar.min.js') }}"></script>
      <script>
         var ps = new PerfectScrollbar('#sidebar');
      </script>
      <!-- custom js -->
      <script src="{{ asset('back/pluto/js/custom.js') }}"></script>
      <script src="{{ asset('back/pluto/js/chart_custom_style1.js') }}"></script>
      @stack('scripts')
   </body>
</html>