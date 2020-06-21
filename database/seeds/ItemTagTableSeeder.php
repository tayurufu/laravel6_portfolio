<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemTagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('item_tag')->truncate();

        //
        for($i = 1; $i <= 3; $i++){
            for($j = 1; $j <= 10; $j++) {
                DB::table('item_tag')->insert([
                    'item_name' => 'item_test' . $i,
                    'tag_id' => $j,
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
