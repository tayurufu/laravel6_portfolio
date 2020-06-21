<?php

use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Faker\Factory as Faker;

class ItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Item::truncate();

        $faker = Faker::create('ja_JP');

        //
        for($i = 1; $i <= 5; $i++) {
            Item::create([
                'name' => 'item_test'. $i,
                'price' => 1000 * $i,
                'display_name' => 'item_test_display_' . $i,
                'type_id' => \App\Models\ItemType::inRandomOrder()->first()->id,
                'description' => $faker->text(500)
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
