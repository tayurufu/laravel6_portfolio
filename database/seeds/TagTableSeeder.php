<?php

use Illuminate\Database\Seeder;

use App\Models\Tag;

class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Tag::truncate();

        for($i = 1; $i <= 10; $i++) {
            $tag = new Tag(
                [
                    'name' => 'tag' . $i
                ]
            );
            $tag->save();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
