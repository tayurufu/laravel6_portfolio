<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();

        //
        $roles = [
            'admin',
            'manager',
            'staff',
            'customer'
        ];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
