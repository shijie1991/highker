<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminRoleMenuTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_role_menu')->delete();

        \DB::table('admin_role_menu')->insert([
            0 => [
                'role_id'    => 1,
                'menu_id'    => 2,
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'role_id'    => 1,
                'menu_id'    => 8,
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'role_id'    => 1,
                'menu_id'    => 9,
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'role_id'    => 1,
                'menu_id'    => 10,
                'created_at' => null,
                'updated_at' => null,
            ],
            4 => [
                'role_id'    => 1,
                'menu_id'    => 11,
                'created_at' => null,
                'updated_at' => null,
            ],
            5 => [
                'role_id'    => 1,
                'menu_id'    => 12,
                'created_at' => null,
                'updated_at' => null,
            ],
            6 => [
                'role_id'    => 1,
                'menu_id'    => 13,
                'created_at' => null,
                'updated_at' => null,
            ],
            7 => [
                'role_id'    => 1,
                'menu_id'    => 14,
                'created_at' => null,
                'updated_at' => null,
            ],
            8 => [
                'role_id'    => 1,
                'menu_id'    => 16,
                'created_at' => null,
                'updated_at' => null,
            ],
            9 => [
                'role_id'    => 1,
                'menu_id'    => 18,
                'created_at' => null,
                'updated_at' => null,
            ],
            10 => [
                'role_id'    => 1,
                'menu_id'    => 19,
                'created_at' => null,
                'updated_at' => null,
            ],
            11 => [
                'role_id'    => 1,
                'menu_id'    => 20,
                'created_at' => null,
                'updated_at' => null,
            ],
            12 => [
                'role_id'    => 2,
                'menu_id'    => 2,
                'created_at' => null,
                'updated_at' => null,
            ],
            13 => [
                'role_id'    => 2,
                'menu_id'    => 16,
                'created_at' => null,
                'updated_at' => null,
            ],
            14 => [
                'role_id'    => 2,
                'menu_id'    => 19,
                'created_at' => null,
                'updated_at' => null,
            ],
            15 => [
                'role_id'    => 2,
                'menu_id'    => 20,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
