<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $products = [
            ['name' => 'Бездротові навушники', 'category' => 'Електроніка', 'sku' => 'EL-1001', 'price' => 1490, 'wholesale_price' => 1100, 'unit' => 'шт', 'stock' => 42, 'min_alert' => 10, 'image' => 'product-1.png'],
            ['name' => 'Ігровий джойстик', 'category' => 'Електроніка', 'sku' => 'EL-1002', 'price' => 990, 'wholesale_price' => null, 'unit' => 'шт', 'stock' => 8, 'min_alert' => 10, 'image' => 'product-2.png'],
            ['name' => 'Smart Watch Pro', 'category' => 'Електроніка', 'sku' => 'EL-1003', 'price' => 3200, 'wholesale_price' => 2600, 'unit' => 'шт', 'stock' => 0, 'min_alert' => 5, 'image' => 'product-3.png'],
            ['name' => 'USB-C зарядний пристрій', 'category' => 'Аксесуари', 'sku' => 'AC-2001', 'price' => 350, 'wholesale_price' => 220, 'unit' => 'шт', 'stock' => 65, 'min_alert' => 15, 'image' => 'product-4.png'],
            ['name' => 'Портативна Bluetooth колонка', 'category' => 'Електроніка', 'sku' => 'EL-1004', 'price' => 1750, 'wholesale_price' => null, 'unit' => 'шт', 'stock' => 3, 'min_alert' => 5, 'image' => 'product-5.png'],
            ['name' => 'Механічна клавіатура RGB', 'category' => 'Електроніка', 'sku' => 'EL-1005', 'price' => 2450, 'wholesale_price' => 1900, 'unit' => 'шт', 'stock' => 19, 'min_alert' => 5, 'image' => 'product-6.png'],
            ['name' => 'MacBook Pro 16"', 'category' => 'Електроніка', 'sku' => 'EL-1006', 'price' => 89990, 'wholesale_price' => null, 'unit' => 'шт', 'stock' => 2, 'min_alert' => 3, 'image' => 'product-7.png'],
            ['name' => 'Провідні навушники', 'category' => 'Аксесуари', 'sku' => 'AC-2002', 'price' => 590, 'wholesale_price' => 380, 'unit' => 'шт', 'stock' => 0, 'min_alert' => 10, 'image' => 'product-8.png'],
            ['name' => 'Магнітна клавіатура', 'category' => 'Аксесуари', 'sku' => 'AC-2003', 'price' => 4200, 'wholesale_price' => 3500, 'unit' => 'шт', 'stock' => 27, 'min_alert' => 5, 'image' => 'product-9.png'],
            ['name' => 'Захисне скло для телефону', 'category' => 'Аксесуари', 'sku' => 'AC-2004', 'price' => 150, 'wholesale_price' => 70, 'unit' => 'шт', 'stock' => 120, 'min_alert' => 20, 'image' => 'product-10.png'],
            ['name' => 'Кулькові ручки (набір)', 'category' => 'Канцелярія', 'sku' => 'KN-3001', 'price' => 85, 'wholesale_price' => 45, 'unit' => 'упаковка', 'stock' => 6, 'min_alert' => 10, 'image' => null],
            ['name' => 'Бавовняна футболка', 'category' => 'Одяг', 'sku' => 'OD-4001', 'price' => 450, 'wholesale_price' => 280, 'unit' => 'шт', 'stock' => 0, 'min_alert' => 5, 'image' => null],
        ];

        foreach ($products as $item) {
            Product::create([
                'category_id' => $categories[$item['category']],
                'name' => $item['name'],
                'sku' => $item['sku'],
                'description' => "Опис товару «{$item['name']}» — якісний товар з категорії «{$item['category']}».",
                'image_path' => $item['image'] ? 'assets/images/'.$item['image'] : null,
                'price' => $item['price'],
                'wholesale_price' => $item['wholesale_price'],
                'unit' => $item['unit'],
                'stock_quantity' => $item['stock'],
                'min_stock_alert' => $item['min_alert'],
                'is_active' => true,
            ]);
        }
    }
}
