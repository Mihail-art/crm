<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <title>Вхід - {{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon_io/site.webmanifest') }}">

    @vite(['resources/js/theme.js'])

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
    </style>
</head>

<body>

    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card" style="max-width:420px; width:100%;">
            <div class="card-body p-5">
                <div class="text-center mb-3">
                    <a href="{{ url('/') }}" class="mb-4 d-inline-block">
                        <img src="{{ asset('assets/images/logo-icon.svg') }}" alt="" width="36">
                        <span class="ms-2"><img src="{{ asset('assets/images/logo.svg') }}" alt=""></span>
                    </a>
                    <h1 class="card-title mb-5 h5">Увійдіть у свій акаунт</h1>
                </div>

                <form class="needs-validation mt-3" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Електронна пошта</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        <div class="invalid-feedback">{{ $errors->first('email') ?: 'Введіть коректну електронну пошту.' }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label d-flex justify-content-between">
                            <span>Пароль</span>
                            <a href="#" class="small link-primary">Забули пароль?</a>
                        </label>
                        <input id="password" name="password" type="password" class="form-control" placeholder="Пароль" required minlength="6">
                        <div class="invalid-feedback">Введіть пароль (мінімум 6 символів).</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input id="remember" name="remember" class="form-check-input" type="checkbox">
                            <label class="form-check-label small" for="remember">Запам'ятати мене</label>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Увійти</button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    Немає акаунта? <a href="{{ route('register') }}" class="link-primary">Зареєструватися</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
