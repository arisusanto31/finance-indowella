<?php
    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Stock;
use App\Models\StockCategory;

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
                ['name' => 'Gelas Plastik 12oz','category_id' =>1,'parent_category_id' =>1, 'unit_backend' =>'Pcs'],
                ['name' => 'Kertas Nasi 22*27 p500','category_id' =>2,'parent_category_id' =>2, 'unit_backend' =>'Pcs'],  
            ];
            foreach( $categories as $category) {
                StockCategory::create($category);
            }
    
            foreach ($items as $item) {
                Stock::create($item);
            }
        }
    }
    