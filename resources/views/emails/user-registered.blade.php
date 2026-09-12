<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Ваш логін</title>
</head>
<body style="font-family: sans-serif; background: #f5f5f5; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0;">Вітаємо, {{ $user->name }}!</h2>
        <p>Ваш акаунт на <strong>{{ config('app.name') }}</strong> успішно створено.</p>
        <p>Ваш логін для входу:</p>
        <p style="font-size: 20px; font-weight: bold; background: #f0f0f0; padding: 12px 16px; border-radius: 6px; text-align: center;">
            {{ $user->login }}
        </p>
        <p>Використовуйте його разом із паролем, який ви вказали під час реєстрації, щоб увійти в акаунт.</p>
        <p style="color: #888; font-size: 13px;">Якщо ви не реєструвались на {{ config('app.name') }}, просто проігноруйте цей лист.</p>
    </div>
</body>
</html>
