<?php

use Illuminate\Database\Seeder;

class ItemPhotoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('item_photos')->truncate();

        /*
        $files = Storage::disk('photos')->files();
        foreach($files as $file){
            Storage::disk('photos')->delete($file);
        }
        */

        for($i = 1; $i <= 3; $i++) {

            for($j = 1; $j <= 3; $j++) {
                $itemName = 'item_test' . $i;
                $fileName =  $itemName . '_' . uniqid(rand()) . '.jpg';
                DB::table('item_photos')->insert([
                    'item_name' => $itemName,
                    'order' => $j,
                    'filename' => $fileName,
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()'),
                ]);

                //Storage::disk('photos')->copy('test.jpg', $fileName);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}
