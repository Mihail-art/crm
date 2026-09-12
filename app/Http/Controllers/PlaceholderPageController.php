<?php

namespace App\Http\Controllers;

class PlaceholderPageController extends Controller
{
    private const PAGES = [
        'clients' => ['title' => 'Клієнти', 'icon' => 'ti-users'],
        'deals' => ['title' => 'Угоди / Воронка', 'icon' => 'ti-filter'],
        'orders' => ['title' => 'Замовлення', 'icon' => 'ti-shopping-cart'],
        'messages' => ['title' => 'Повідомлення', 'icon' => 'ti-message'],
        'analytics' => ['title' => 'Аналітика', 'icon' => 'ti-chart-bar'],
        'finance' => ['title' => 'Фінанси / Дебіторка', 'icon' => 'ti-wallet'],
        'warehouse' => ['title' => 'Склад', 'icon' => 'ti-building-warehouse'],
        'settings' => ['title' => 'Налаштування', 'icon' => 'ti-settings'],
    ];

    public function show(string $page): \Illuminate\View\View
    {
        $data = self::PAGES[$page];

        return view('pages.placeholder', [
            'title' => $data['title'],
            'icon' => $data['icon'],
        ]);
    }
}
