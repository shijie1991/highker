<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminRolePermissionsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_role_permissions')->delete();

        \DB::table('admin_role_permissions')->insert([
            0 => [
                'role_id'       => 2,
                'permission_id' => 2,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            1 => [
                'role_id'       => 2,
                'permission_id' => 3,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            2 => [
                'role_id'       => 2,
                'permission_id' => 4,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            3 => [
                'role_id'       => 2,
                'permission_id' => 5,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            4 => [
                'role_id'       => 2,
                'permission_id' => 6,
                'created_at'    => null,
                'updated_at'    => null,
            ],
            5 => [
                'role_id'       => 2,
                'permission_id' => 7,
                'created_at'    => null,
                'updated_at'    => null,
            ],
        ]);
    }
}
