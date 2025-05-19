<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockCustomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $category = StockCategory::where('name', 'custom')->first();
        if (!$category) {
            $category = StockCategory::create([
                'name' => 'custom',


            ]);
        }
        $items = [
            [
                'id' => 9991,
                'book_journal_id' => 0,
                'name' => 'stock_custom1',
                'category_id' => $category->id,
                'type' => 'bahan baku',
                'parent_category_id' => null,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Pcs',
                'unit' => ['Pcs' => 1]
            ],
            [
                'id' => 9992,
                'book_journal_id' => 0,
                'name' => 'stock_custom2',
                'category_id' => $category->id,
                'type' => 'bahan baku',
                'parent_category_id' => null,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Pcs',
                'unit' => ['Pcs' => 1]
            ],
            [
                'id' => 9993,
                'book_journal_id' => 0,
                'name' => 'stock_custom3',
                'category_id' => $category->id,
                'type' => 'bahan baku',
                'parent_category_id' => null,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Pcs',
                'unit' => ['Pcs' => 1]
            ],
            [
                'id' => 9994,
                'book_journal_id' => 0,
                'name' => 'stock_custom4',
                'category_id' => $category->id,
                'type' => 'bahan baku',
                'parent_category_id' => null,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Pcs',
                'unit' => ['Pcs' => 1]
            ],
            [
                'id' => 9995,
                'book_journal_id' => 0,
                'name' => 'stock_custom5',
                'category_id' => $category->id,
                'type' => 'bahan baku',
                'parent_category_id' => null,
                'unit_backend' => 'Pcs',
                'unit_default' => 'Pcs',
                'unit' => ['Pcs' => 1]
            ],
        ];
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
