<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <title>Реєстрація - {{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/images/favicon_io/site.webmanifest') }}">

    @vite(['resources/js/theme.js', 'resources/js/auth.js'])

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }
    </style>
</head>

<body @if(session('status')) data-status="{{ session('status') }}" data-status-redirect="{{ route('login') }}" @endif>

    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card" style="max-width:420px; width:100%;">
            <div class="card-body p-5">
                <div class="text-center mb-3">
                    <a href="{{ url('/') }}" class="mb-4 d-inline-block">
                        <img src="{{ asset('assets/images/logo-icon.svg') }}" alt="" width="36">
                        <span class="ms-2"><img src="{{ asset('assets/images/logo.svg') }}" alt=""></span>
                    </a>
                    <h1 class="card-title mb-5 h5">Створіть акаунт</h1>
                </div>

                <form class="needs-validation mt-3" method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="fullName" class="form-label">Повне ім'я</label>
                        <input id="fullName" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Іван Іваненко" value="{{ old('name') }}" required>
                        <div class="invalid-feedback">{{ $errors->first('name') ?: "Введіть ваше ім'я." }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Електронна пошта</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email') }}" required>
                        <div class="invalid-feedback">{{ $errors->first('email') ?: 'Введіть коректну електронну пошту.' }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Створіть пароль" required minlength="6">
                        <div class="invalid-feedback">{{ $errors->first('password') ?: 'Введіть пароль (мінімум 6 символів).' }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Підтвердіть пароль</label>
                        <input id="confirmPassword" name="password_confirmation" type="password" class="form-control" placeholder="Повторіть пароль" required
                            oninput="this.setCustomValidity(document.getElementById('password').value !== this.value ? 'Паролі не співпадають.' : '')">
                        <div class="invalid-feedback">Паролі мають співпадати.</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input id="terms" class="form-check-input" type="checkbox" required>
                        <label class="form-check-label small" for="terms">Я погоджуюсь з <a href="#" class="text-decoration-none">умовами та політикою конфіденційності</a></label>
                        <div class="invalid-feedback">Потрібно погодитись, щоб продовжити.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Зареєструватися</button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    Вже маєте акаунт? <a href="{{ route('login') }}" class="link-primary">Увійти</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
