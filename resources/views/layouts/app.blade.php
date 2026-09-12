<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon_io/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon_io/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon_io/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/images/favicon_io/site.webmanifest') }}">

  @vite(['resources/js/theme.js'])
</head>

<body>
  <div id="overlay" class="overlay"></div>

  <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
    @csrf
  </form>

  <!-- TOPBAR -->
  <nav id="topbar" class="navbar bg-body border-bottom fixed-top topbar px-3">
    <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm ">
      <i class="ti ti-layout-sidebar-left-expand"></i>
    </button>

    <!-- MOBILE -->
    <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none me-2">
      <i class="ti ti-layout-sidebar-left-expand"></i>
    </button>
    <div>
      <!-- Navbar nav -->
      <ul class="list-unstyled d-flex align-items-center mb-0 gap-1">
        <!-- Bell icon -->
        <li>
          <a class="position-relative btn-icon btn-sm btn-light btn rounded-circle" data-bs-toggle="dropdown"
            aria-expanded="false" href="#" role="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
              class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
              <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
            </svg>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-2 ms-n2">
              2
              <span class="visually-hidden">unread messages</span>
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
            <ul class="list-unstyled p-0 m-0">
              <li class="p-3 border-bottom ">
                <div class="d-flex gap-3">
                  <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
                  <div class="flex-grow-1 small">
                    <p class="mb-0">Нове замовлення</p>
                    <p class="mb-1">Замовлення #12345 оформлено</p>
                    <div class="text-secondary">5 хвилин тому</div>
                  </div>
                </div>
              </li>
              <li class="p-3 border-bottom ">
                <div class="d-flex gap-3">
                  <img src="{{ asset('assets/images/avatar/avatar-4.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
                  <div class="flex-grow-1 small">
                    <p class="mb-0">Новий користувач</p>
                    <p class="mb-1">Зареєструвався новий користувач</p>
                    <div class="text-secondary">30 хвилин тому</div>
                  </div>
              </li>

              <li class="p-3 border-bottom">
                <div class="d-flex gap-3">
                  <img src="{{ asset('assets/images/avatar/avatar-2.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
                  <div class="flex-grow-1 small">
                    <p class="mb-0">Оплату підтверджено</p>
                    <p class="mb-1">Отримано оплату $299</p>
                    <div class="text-secondary">1 годину тому</div>
                  </div>
                </div>
              </li>
              <li class="px-4 py-3 text-center">
                <a href="#" class="text-primary ">Переглянути всі сповіщення</a>
              </li>
            </ul>
          </div>
        </li>
        <!-- Dropdown -->
        <li class="ms-3 dropdown">
          <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
          </a>
          <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 200px;">
            <div>
              <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-3 py-3">
                <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-md rounded-circle" />
                <div>
                  <h4 class="mb-0 small">{{ auth()->user()->name }}</h4>
                  <p class="mb-0 small">{{ '@' . auth()->user()->login }}</p>
                </div>
              </div>
              <div class="p-3 d-flex flex-column gap-1 small lh-lg">
                <a href="#!"><span>Головна</span></a>
                <a href="#!"><span>Вхідні</span></a>
                <a href="#!"><span>Чат</span></a>
                <a href="#!"><span>Активність</span></a>
                <a href="#!"><span>Налаштування акаунта</span></a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger">
                  <span>Вийти</span>
                </a>
              </div>

            </div>
          </div>
        </li>
      </ul>
    </div>

  </nav>

  <!-- SIDEBAR -->
  <aside id="sidebar" class="sidebar">
    <div class="logo-area">
     <a href="{{ route('dashboard') }}" class="d-inline-flex"><img src="{{ asset('assets/images/logo-icon.svg') }}" alt="" width="24">
        <span class="logo-text ms-2"> <img src="{{ asset('assets/images/logo.svg') }}" alt=""></span>
      </a>
    </div>
    <ul class="nav flex-column">
      <li class="px-4 py-2"><small class="nav-text">Основне</small></li>
      @php
        $navItems = [
            ['route' => 'dashboard', 'icon' => 'ti-home', 'label' => 'Дашборд'],
            ['route' => 'clients', 'icon' => 'ti-users', 'label' => 'Клієнти'],
            ['route' => 'deals', 'icon' => 'ti-filter', 'label' => 'Угоди / Воронка'],
            ['route' => 'orders', 'icon' => 'ti-shopping-cart', 'label' => 'Замовлення'],
            ['route' => 'products', 'icon' => 'ti-box-seam', 'label' => 'Товари'],
            ['route' => 'messages', 'icon' => 'ti-message', 'label' => 'Повідомлення'],
            ['route' => 'tasks', 'icon' => 'ti-checklist', 'label' => 'Задачі'],
            ['route' => 'analytics', 'icon' => 'ti-chart-bar', 'label' => 'Аналітика'],
            ['route' => 'finance', 'icon' => 'ti-wallet', 'label' => 'Фінанси / Дебіторка'],
            ['route' => 'warehouse', 'icon' => 'ti-building-warehouse', 'label' => 'Склад'],
            ['route' => 'team', 'icon' => 'ti-users-group', 'label' => 'Команда'],
            ['route' => 'settings', 'icon' => 'ti-settings', 'label' => 'Налаштування'],
        ];
      @endphp
      @foreach ($navItems as $item)
        <li>
          <a class="nav-link @if(request()->routeIs($item['route'])) active @endif" href="{{ route($item['route']) }}">
            <i class="ti {{ $item['icon'] }}"></i><span class="nav-text">{{ $item['label'] }}</span>
          </a>
        </li>
      @endforeach

      <li class="px-4 pt-4 pb-2"><small class="nav-text">Акаунт</small></li>
      <li>
        <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
          <i class="ti ti-logout"></i><span class="nav-text">Вийти</span>
        </a>
      </li>
    </ul>

  </aside>

  <!-- MAIN CONTENT -->
  <main id="content" class="content py-10">
    <div class="container-fluid">
      @yield('content')

      <div class="row">
        <div class="col-12">
          <footer class="text-center py-2 mt-6 text-secondary ">
            <p class="mb-0">© {{ date('Y') }} {{ config('app.name') }}</p>
          </footer>
        </div>
      </div>
    </div>
  </main>

  @stack('scripts')
</body>

</html>
