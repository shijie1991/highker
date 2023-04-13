<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminPermissionMenuTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_permission_menu')->delete();

        \DB::table('admin_permission_menu')->insert([
            0 => [
                'permission_id' => 2,
                'menu_id'       => 8,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            1 => [
                'permission_id' => 3,
                'menu_id'       => 8,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            2 => [
                'permission_id' => 4,
                'menu_id'       => 8,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            3 => [
                'permission_id' => 5,
                'menu_id'       => 8,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            4 => [
                'permission_id' => 6,
                'menu_id'       => 8,
                'created_at'    => null,
                'updated_at'    => null,
            ],
        ]);
    }
}
