<?php

use Illuminate\Database\Seeder;

class StockTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Stock::truncate();

        $items = App\Models\Item::all();

        foreach($items as $k => $item){
            \App\Models\Stock::create([
                'item_name' => $item->name,
                'location_id' => 1,
                'stock' => 10,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
