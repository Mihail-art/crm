<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        collect(['Електроніка', 'Аксесуари', 'Побутова техніка', 'Канцелярія', 'Одяг'])
            ->each(fn (string $name) => Category::firstOrCreate(['name' => $name]));
    }
}
