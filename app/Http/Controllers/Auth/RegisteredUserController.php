<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserRegisteredMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.signup');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => "Введіть ваше ім'я.",
            'name.max' => "Ім'я занадто довге.",
            'email.required' => 'Введіть електронну пошту.',
            'email.email' => 'Введіть коректну електронну пошту.',
            'email.unique' => 'Ця електронна пошта вже зареєстрована.',
            'password.required' => 'Введіть пароль.',
            'password.min' => 'Пароль має містити мінімум 6 символів.',
            'password.confirmed' => 'Паролі не співпадають.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'login' => $this->generateUniqueLogin($validated['name']),
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        Mail::to($user->email)->send(new UserRegisteredMail($user));

        return redirect()->route('register')->with('status', 'Реєстрація успішна! Логін надіслано на вашу пошту.');
    }

    private function generateUniqueLogin(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'user';

        do {
            $login = $base . '_' . random_int(1000, 9999);
        } while (User::where('login', $login)->exists());

        return $login;
    }
}
