<?php

use Illuminate\Database\Seeder;

class StockLocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\StockLocation::truncate();

        //
        for($i = 1; $i <= 5; $i++) {
            \App\Models\StockLocation::create([
                'name' => 'test_location_'. $i,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
