<x-laravel-ui-adminlte::adminlte-layout>

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                                class="fas fa-bars"></i></a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('assets/admin/img/ci.png') }}"
                                class="user-image img-circle elevation-2" alt="Logo">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-gray">
                                <img src="{{ asset('assets/admin/img/ci.png') }}"
                                    class="img-circle elevation-2" alt="Logo">
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <a href="{{ route('admin.profile.edit') }}" class="btn btn-default btn-flat">個人資料</a>
                                <a href="#" class="btn btn-default btn-flat float-right" id="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> 登出
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                {{-- 到期提醒橫幅（只對子帳號顯示） --}}
                @auth
                @if(Auth::user()->isSubUser() && Auth::user()->expires_at)
                    @php
                        $daysLeft = now()->diffInDays(Auth::user()->expires_at, false);
                    @endphp
                    @if($daysLeft <= 7 && $daysLeft >= 0)
                    <div class="alert alert-danger mb-0 rounded-0 text-center">
                        <i class="fas fa-exclamation-triangle"></i>
                        您的帳號將於 {{ Auth::user()->expires_at->format('Y-m-d') }} 到期，剩餘 {{ $daysLeft }} 天。
                        @if(\App\Models\SystemSetting::canUserAccessRenewal(Auth::id()))
                            <a href="{{ route('renewal.index') }}" class="alert-link">立即續約</a>
                        @else
                            <span>請聯繫管理員進行續約</span>
                        @endif
                    </div>
                    @elseif($daysLeft <= 30 && $daysLeft >= 0)
                    <div class="alert alert-warning mb-0 rounded-0 text-center">
                        <i class="fas fa-info-circle"></i>
                        您的帳號將於 {{ Auth::user()->expires_at->format('Y-m-d') }} 到期，剩餘 {{ $daysLeft }} 天。
                        @if(\App\Models\SystemSetting::canUserAccessRenewal(Auth::id()))
                            <a href="{{ route('renewal.index') }}" class="alert-link ml-2">前往續約</a>
                        @else
                            <span class="ml-2">請聯繫管理員進行續約</span>
                        @endif
                    </div>
                    @elseif($daysLeft < 0)
                    <div class="alert alert-danger mb-0 rounded-0 text-center">
                        <i class="fas fa-times-circle"></i>
                        您的帳號已過期，請立即續約以恢復使用。
                        @if(\App\Models\SystemSetting::canUserAccessRenewal(Auth::id()))
                            <a href="{{ route('renewal.index') }}" class="alert-link ml-2">立即續約</a>
                        @else
                            <span class="ml-2">請聯繫管理員進行續約</span>
                        @endif
                    </div>
                    @endif
                @endif
                @endauth
                @yield('content')
            </div>

            <!-- Main Footer -->
            {{-- <footer class="main-footer">
                <div class="float-right d-none d-sm-block">
                    <b>Version</b> 3.1.0
                </div>
                <strong>Copyright &copy; 2014-2023 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights
                reserved.
            </footer> --}}
        </div>
    </body>
    @push('page_scripts')
        <script @cspNonce>
            document.addEventListener('DOMContentLoaded', () => {
                const logoutLink = document.getElementById('logout-link');
                const logoutForm = document.getElementById('logout-form');
                if (logoutLink && logoutForm) {
                    logoutLink.addEventListener('click', (event) => {
                        event.preventDefault();
                        logoutForm.submit();
                    });
                }
            });
        </script>
    @endpush
</x-laravel-ui-adminlte::adminlte-layout>
