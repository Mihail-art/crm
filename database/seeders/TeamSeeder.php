<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Олена Ковальчук', 'role' => 'manager', 'department' => 'sales', 'phone' => '+380 67 111 22 33', 'hire' => '2024-03-10', 'avatar' => 'avatar-1.jpg', 'active' => true],
            ['name' => 'Дмитро Іваненко', 'role' => 'manager', 'department' => 'sales', 'phone' => '+380 67 222 33 44', 'hire' => '2024-05-22', 'avatar' => 'avatar-2.jpg', 'active' => true],
            ['name' => 'Анна Петренко', 'role' => 'admin', 'department' => 'support', 'phone' => '+380 67 333 44 55', 'hire' => '2023-11-01', 'avatar' => 'avatar-3.jpg', 'active' => true],
            ['name' => 'Сергій Бондаренко', 'role' => 'accountant', 'department' => null, 'phone' => '+380 67 444 55 66', 'hire' => '2023-08-15', 'avatar' => 'avatar-4.jpg', 'active' => true],
            ['name' => 'Марина Ткаченко', 'role' => 'manager', 'department' => 'sales', 'phone' => '+380 67 555 66 77', 'hire' => '2025-01-20', 'avatar' => 'avatar-5.jpg', 'active' => false],
            ['name' => 'Віктор Мельник', 'role' => 'admin', 'department' => 'warehouse', 'phone' => '+380 67 666 77 88', 'hire' => '2024-09-05', 'avatar' => 'avatar-6.jpg', 'active' => true],
            ['name' => 'Наталія Шевченко', 'role' => 'manager', 'department' => 'support', 'phone' => '+380 67 777 88 99', 'hire' => '2025-04-12', 'avatar' => null, 'active' => true],
        ];

        foreach ($members as $item) {
            $login = Str::slug($item['name'], '_');

            User::firstOrCreate(
                ['email' => $login.'@example.com'],
                [
                    'name' => $item['name'],
                    'login' => $login,
                    'phone' => $item['phone'],
                    'password' => Str::random(16),
                    'role' => $item['role'],
                    'department' => $item['department'],
                    'hire_date' => $item['hire'],
                    'avatar_path' => $item['avatar'] ? 'assets/images/avatar/'.$item['avatar'] : null,
                    'is_active' => $item['active'],
                ]
            );
        }
    }
}
