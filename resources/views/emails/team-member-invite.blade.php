<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>{{ $isNewInvite ? 'Запрошення до команди' : 'Пароль оновлено' }}</title>
</head>
<body style="font-family: sans-serif; background: #f5f5f5; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0;">Вітаємо, {{ $user->name }}!</h2>

        @if ($isNewInvite)
            <p>Вас додано до команди <strong>{{ config('app.name') }}</strong> з роллю «{{ $user->roleLabel() }}».</p>
        @else
            <p>Для вашого акаунта на <strong>{{ config('app.name') }}</strong> було згенеровано новий пароль.</p>
        @endif

        <p>Ваш логін:</p>
        <p style="font-size: 18px; font-weight: bold; background: #f0f0f0; padding: 10px 16px; border-radius: 6px; text-align: center;">
            {{ $user->login }}
        </p>

        <p>Ваш пароль:</p>
        <p style="font-size: 18px; font-weight: bold; background: #f0f0f0; padding: 10px 16px; border-radius: 6px; text-align: center;">
            {{ $password }}
        </p>

        <p>Радимо змінити пароль після першого входу.</p>
        <p style="color: #888; font-size: 13px;">Якщо ви не очікували цей лист, зверніться до адміністратора {{ config('app.name') }}.</p>
    </div>
</body>
</html>
