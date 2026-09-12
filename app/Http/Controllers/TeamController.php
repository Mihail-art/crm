<?php

namespace App\Http\Controllers;

use App\Mail\TeamMemberInviteMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public const PLAN_SEAT_LIMIT = 8;

    public function index(Request $request): \Illuminate\View\View
    {
        $query = User::query();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        match ($request->string('status')->toString()) {
            'active' => $query->where('is_active', true),
            'blocked' => $query->where('is_active', false),
            default => null,
        };

        $team = $query->orderBy('name')->paginate(12)->withQueryString();

        $totalCount = User::count();
        $activeCount = User::where('is_active', true)->count();
        $managerCount = User::where('role', 'manager')->count();

        return view('pages.team.index', [
            'team' => $team,
            'filters' => $request->only(['search', 'role', 'status']),
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'managerCount' => $managerCount,
            'seatLimit' => self::PLAN_SEAT_LIMIT,
            'limitReached' => $totalCount >= self::PLAN_SEAT_LIMIT,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (User::count() >= self::PLAN_SEAT_LIMIT) {
            return redirect()->route('team')->with('error', 'Ліміт тарифу вичерпано. Оновіть план, щоб додати нового співробітника.');
        }

        $validated = $this->withAvatar($request, $this->validated($request));
        $password = Str::password(10, symbols: false);

        $user = User::create([
            ...$validated,
            'login' => $this->generateUniqueLogin($validated['name']),
            'password' => $password,
        ]);

        if ($request->boolean('send_invite')) {
            Mail::to($user->email)->send(new TeamMemberInviteMail($user, $password, true));
        }

        return redirect()->route('team')->with('status', 'Співробітника додано.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validated($request, $user);

        $user->update($this->withAvatar($request, $validated, $user));

        return redirect()->route('team')->with('status', 'Дані співробітника оновлено.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('team')->with('error', 'Ви не можете видалити власний акаунт.');
        }

        if ($user->avatar_path && str_starts_with($user->avatar_path, 'storage/')) {
            Storage::disk('public')->delete(substr($user->avatar_path, strlen('storage/')));
        }

        $user->delete();

        return redirect()->route('team')->with('status', 'Співробітника видалено.');
    }

    public function toggleActive(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Неможливо заблокувати власний акаунт.'], 422);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return response()->json(['is_active' => $user->is_active]);
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $password = Str::password(10, symbols: false);
        $user->update(['password' => $password]);

        Mail::to($user->email)->send(new TeamMemberInviteMail($user, $password, false));

        return redirect()->route('team')->with('status', "Новий пароль надіслано на {$user->email}.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.($user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:'.implode(',', array_keys(User::ROLES))],
            'department' => ['nullable', 'in:'.implode(',', array_keys(User::DEPARTMENTS))],
            'hire_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ], [
            'name.required' => "Введіть ім'я та прізвище.",
            'email.required' => 'Введіть електронну пошту.',
            'email.email' => 'Введіть коректну електронну пошту.',
            'email.unique' => 'Ця електронна пошта вже використовується.',
            'role.required' => 'Оберіть роль.',
            'role.in' => 'Оберіть коректну роль.',
            'avatar.image' => 'Файл має бути зображенням.',
            'avatar.max' => 'Розмір зображення не має перевищувати 4 МБ.',
        ]);
    }

    private function withAvatar(Request $request, array $validated, ?User $user = null): array
    {
        unset($validated['avatar']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('avatar')) {
            if ($user?->avatar_path && str_starts_with($user->avatar_path, 'storage/')) {
                Storage::disk('public')->delete(substr($user->avatar_path, strlen('storage/')));
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar_path'] = 'storage/'.$path;
        }

        return $validated;
    }

    private function generateUniqueLogin(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'user';

        do {
            $login = $base.'_'.random_int(1000, 9999);
        } while (User::where('login', $login)->exists());

        return $login;
    }
}
