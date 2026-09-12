<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'login', 'email', 'phone', 'password', 'role', 'department', 'hire_date', 'avatar_path', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'owner' => 'Керівник',
        'manager' => 'Менеджер',
        'admin' => 'Адміністратор',
        'accountant' => 'Бухгалтер',
    ];

    public const ROLE_DESCRIPTIONS = [
        'owner' => 'Повний доступ до всіх розділів та налаштувань системи.',
        'manager' => 'Бачить тільки свої угоди та клієнтів.',
        'admin' => 'Керує командою, товарами та налаштуваннями.',
        'accountant' => 'Доступ до фінансів, звітів та дебіторки.',
    ];

    public const ROLE_BADGES = [
        'owner' => 'badge-role-owner',
        'manager' => 'badge-role-manager',
        'admin' => 'bg-secondary-subtle text-secondary-emphasis',
        'accountant' => 'bg-success-subtle text-success-emphasis',
    ];

    public const DEPARTMENTS = [
        'sales' => 'Продажі',
        'warehouse' => 'Склад',
        'support' => 'Підтримка',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $letters = array_map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)), array_filter($parts));

        return implode('', array_slice($letters, 0, 2)) ?: '?';
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function roleDescription(): string
    {
        return self::ROLE_DESCRIPTIONS[$this->role] ?? '';
    }

    public function roleBadgeClass(): string
    {
        return self::ROLE_BADGES[$this->role] ?? 'bg-secondary-subtle text-secondary-emphasis';
    }

    public function departmentLabel(): ?string
    {
        return $this->department ? (self::DEPARTMENTS[$this->department] ?? $this->department) : null;
    }

    public function avatarColor(): string
    {
        $palette = ['#E66239', '#3b82f6', '#00C951', '#c084fc', '#F0B100', '#00B8DB'];

        return $palette[$this->id % count($palette)];
    }
}
