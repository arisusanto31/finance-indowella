<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;

class StockSeeder extends Seeder
{
    public function run(): void
    {

        $categories = [
            ['name' => 'plastik'],
            ['name' => 'kertas bungkus'],
            ['name' => 'kotak nasi'],
            ['name' => 'alat hajatan'],
        ];

        $items = [
            [
                'book_journal_id' => 2,
                'name' => 'Gelas Plastik 12oz',
                'category_id' => 1,
                'type' => 'barang dagang',
                'parent_category_id' => 1,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Slop',
                'unit' => ['Pcs' => 1, 'Slop' => 50, 'Dus' => 1000]
            ],
            [
                'book_journal_id' => 2,
                'name' => 'Kertas Nasi 22*27 p500',
                'type' => 'barang jadi',
                'category_id' => 2,
                'parent_category_id' => 2,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Rim',
                'unit' => ['Pcs' => 1, 'Rim' => 500]
            ],
            [
                'book_journal_id' => 2,
                'type' => 'bahan baku',
                'name' => 'Kertas ROLL 28 gsm 110cm',
                'category_id' => 2,
                'parent_category_id' => 2,
                'unit_backend' => 'Meter',
                'unit_default' => 'Meter',
                'unit' => ['Meter' => 1]
            ],
        ];
        foreach ($categories as $category) {
            StockCategory::create($category);
        }

        foreach ($items as $item) {
            $input = collect($item)->only('name', 'category_id', 'parent_category_id', 'unit_backend', 'unit_default', 'book_journal_id', 'type')->toArray();
            $st = Stock::create($input);
            foreach ($item['unit'] as $key => $value) {
                StockUnit::create([
                    'stock_id' => $st->id,
                    'unit' => $key,
                    'konversi' => $value,

                ]);
            }
        }
    }
}
