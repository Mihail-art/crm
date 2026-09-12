<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        if (Task::count() > 0) {
            return;
        }

        $users = User::orderBy('id')->pluck('id')->values();
        $u = fn (int $i) => $users[$i % $users->count()];
        $creator = $users->first();

        $tasks = [
            ['title' => 'Перевірити залишки на складі', 'desc' => 'Звірити фактичні залишки з системою обліку', 'priority' => 'medium', 'status' => 'new', 'tag' => 'warehouse', 'due' => now()->addDays(3), 'assignee' => 1],
            ['title' => 'Зателефонувати клієнту щодо доставки', 'desc' => 'Уточнити зручний час доставки замовлення №4521', 'priority' => 'high', 'status' => 'new', 'tag' => 'clients', 'due' => now()->addHours(5), 'assignee' => 2, 'client' => 'ФОП Іваненко'],
            ['title' => 'Підготувати акти звірки', 'desc' => 'За жовтень для трьох ключових контрагентів', 'priority' => 'low', 'status' => 'new', 'tag' => 'admin', 'due' => now()->addWeek(), 'assignee' => 3],
            ['title' => 'Оновити прайс-лист на сайті', 'desc' => null, 'priority' => 'medium', 'status' => 'new', 'tag' => 'admin', 'due' => now()->addDays(2), 'assignee' => 0],

            ['title' => 'Зібрати замовлення №4522', 'desc' => 'Комплектація 12 позицій, пакування', 'priority' => 'urgent', 'status' => 'in_progress', 'tag' => 'warehouse', 'due' => now()->addHours(2), 'assignee' => 1],
            ['title' => 'Погодити знижку для оптового клієнта', 'desc' => 'Клієнт запросив знижку 15% на об\'єм від 500 од.', 'priority' => 'high', 'status' => 'in_progress', 'tag' => 'clients', 'due' => now()->addDay(), 'assignee' => 2, 'client' => 'ТОВ Будпостач'],
            ['title' => 'Оформити повернення товару', 'desc' => 'Брак партії USB-кабелів', 'priority' => 'medium', 'status' => 'in_progress', 'tag' => 'warehouse', 'due' => now()->subDay(), 'assignee' => 3],
            ['title' => 'Розробити план доставок на тиждень', 'desc' => null, 'priority' => 'medium', 'status' => 'in_progress', 'tag' => 'delivery', 'due' => now()->addDays(4), 'assignee' => 4],

            ['title' => 'Перевірити рахунок-фактуру №118', 'desc' => 'Звірити суми та реквізити перед відправкою', 'priority' => 'high', 'status' => 'review', 'tag' => 'admin', 'due' => now()->subHours(6), 'assignee' => 3],
            ['title' => 'Узгодити маршрут кур\'єра', 'desc' => 'Оптимізувати маршрут на 8 адрес', 'priority' => 'low', 'status' => 'review', 'tag' => 'delivery', 'due' => now()->addDays(1), 'assignee' => 4],
            ['title' => 'Перевірити нову партію товару', 'desc' => 'Вхідний контроль якості', 'priority' => 'medium', 'status' => 'review', 'tag' => 'warehouse', 'due' => now()->addHours(10), 'assignee' => 1],

            ['title' => 'Закрити угоду з клієнтом Альфа Груп', 'desc' => 'Підписано договір, оплата отримана', 'priority' => 'high', 'status' => 'done', 'tag' => 'clients', 'due' => now()->subDays(2), 'assignee' => 2, 'client' => 'Альфа Груп'],
            ['title' => 'Провести інвентаризацію складу №2', 'desc' => null, 'priority' => 'medium', 'status' => 'done', 'tag' => 'warehouse', 'due' => now()->subDays(5), 'assignee' => 1],
            ['title' => 'Надіслати комерційну пропозицію', 'desc' => 'Новому потенційному клієнту', 'priority' => 'low', 'status' => 'done', 'tag' => 'clients', 'due' => now()->subDays(3), 'assignee' => 2],
            ['title' => 'Оновити договір поставки', 'desc' => 'Додати нові умови оплати', 'priority' => 'medium', 'status' => 'done', 'tag' => 'admin', 'due' => now()->subWeek(), 'assignee' => 3],
        ];

        foreach ($tasks as $position => $item) {
            $task = Task::create([
                'title' => $item['title'],
                'description' => $item['desc'] ?? null,
                'assignee_id' => $u($item['assignee']),
                'creator_id' => $creator,
                'due_date' => $item['due'],
                'priority' => $item['priority'],
                'status' => $item['status'],
                'tag' => $item['tag'],
                'client_name' => $item['client'] ?? null,
                'position' => $position,
            ]);

            if ($position % 3 === 0) {
                $task->checklistItems()->createMany([
                    ['title' => 'Уточнити деталі', 'is_done' => true, 'position' => 0],
                    ['title' => 'Виконати основну дію', 'is_done' => $item['status'] === 'done', 'position' => 1],
                    ['title' => 'Повідомити відповідального', 'is_done' => false, 'position' => 2],
                ]);
            }

            if ($position % 4 === 0) {
                $task->comments()->create([
                    'user_id' => $creator,
                    'body' => 'Прошу пришвидшити виконання цієї задачі.',
                ]);
            }
        }
    }
}
