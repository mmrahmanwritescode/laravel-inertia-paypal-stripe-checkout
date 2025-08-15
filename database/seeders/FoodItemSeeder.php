<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use Illuminate\Database\Seeder;

class FoodItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodItems = [
            [
                'name' => 'Margherita Pizza',
                'description' => 'Classic pizza with fresh tomato sauce, mozzarella cheese, and basil',
                'price' => 12.99,
                'is_active' => true,
            ],
            [
                'name' => 'Chicken Burger',
                'description' => 'Grilled chicken breast with lettuce, tomato, and mayo on a brioche bun',
                'price' => 9.99,
                'is_active' => true,
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with caesar dressing, croutons, and parmesan cheese',
                'price' => 7.99,
                'is_active' => true,
            ],
            [
                'name' => 'Fish & Chips',
                'description' => 'Beer battered cod with crispy fries and mushy peas',
                'price' => 14.99,
                'is_active' => true,
            ],
            [
                'name' => 'Pasta Carbonara',
                'description' => 'Spaghetti with bacon, eggs, parmesan cheese, and black pepper',
                'price' => 11.99,
                'is_active' => true,
            ],
            [
                'name' => 'Chocolate Brownie',
                'description' => 'Rich chocolate brownie served with vanilla ice cream',
                'price' => 5.99,
                'is_active' => true,
            ],
        ];

        foreach ($foodItems as $item) {
            FoodItem::create($item);
        }
    }
}
