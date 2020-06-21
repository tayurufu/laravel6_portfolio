<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\ItemType::truncate();

        //
        for($i = 1; $i <= 5; $i++){
            \App\Models\ItemType::create([
                'name' => 'name_' . $i
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
