<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['name' => 'Espresso', 'description' => 'Single shot espresso, kuat dan pekat', 'price' => 18000, 'category' => 'coffee'],
            ['name' => 'Americano', 'description' => 'Espresso dengan tambahan air panas', 'price' => 22000, 'category' => 'coffee'],
            ['name' => 'Cappuccino', 'description' => 'Espresso dengan steamed milk dan foam', 'price' => 28000, 'category' => 'coffee'],
            ['name' => 'Latte', 'description' => 'Espresso dengan steamed milk lembut', 'price' => 30000, 'category' => 'coffee'],
            ['name' => 'Flat White', 'description' => 'Double espresso dengan microfoam milk', 'price' => 32000, 'category' => 'coffee'],
            ['name' => 'Caramel Macchiato', 'description' => 'Latte dengan caramel sauce', 'price' => 35000, 'category' => 'coffee'],
            ['name' => 'Cold Brew', 'description' => 'Kopi cold brew 12 jam, smooth dan refreshing', 'price' => 32000, 'category' => 'coffee'],
            ['name' => 'Es Kopi Susu', 'description' => 'Kopi susu khas Indonesia dengan gula aren', 'price' => 25000, 'category' => 'coffee'],
            ['name' => 'Matcha Latte', 'description' => 'Green tea matcha dengan steamed milk', 'price' => 30000, 'category' => 'non-coffee'],
            ['name' => 'Chocolate', 'description' => 'Hot chocolate creamy', 'price' => 25000, 'category' => 'non-coffee'],
            ['name' => 'Taro Latte', 'description' => 'Taro purple dengan susu segar', 'price' => 30000, 'category' => 'non-coffee'],
            ['name' => 'Lemon Tea', 'description' => 'Teh dengan perasan lemon segar', 'price' => 20000, 'category' => 'non-coffee'],
            ['name' => 'Croissant', 'description' => 'Croissant butter lapis-lapis', 'price' => 22000, 'category' => 'food'],
            ['name' => 'Banana Cake', 'description' => 'Cake pisang lembut dan moist', 'price' => 25000, 'category' => 'food'],
            ['name' => 'Sandwich', 'description' => 'Sandwich ayam dengan sayuran segar', 'price' => 35000, 'category' => 'food'],
            ['name' => 'Waffle', 'description' => 'Waffle dengan butter dan maple syrup', 'price' => 30000, 'category' => 'food'],
        ];

        foreach ($menus as $menu) {
            Menu::create(array_merge($menu, ['is_available' => true]));
        }
    }
}
