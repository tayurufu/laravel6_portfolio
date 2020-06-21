<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            // php artisan cache:forget spatie.permission.cache
            PermissionTableSeeder::class,
            RoleTableSeeder::class,
            AclUserTableSeeder::class,
            RoleHasPermissionTableSeeder::class,

            CustomerTableSeeder::class,
            StockLocationTableSeeder::class,
            ItemTypeTableSeeder::class,
            TagTableSeeder::class,
            ItemTableSeeder::class,
            ItemTagTableSeeder::class,
            ItemPhotoTableSeeder::class,
            StockTableSeeder::class,
            OrderTableSeeder::class,

        ]);
    }
}
