<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminRolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_roles')->delete();

        \DB::table('admin_roles')->insert([
            0 => [
                'id'         => 1,
                'name'       => '超级管理员',
                'slug'       => 'administrator',
                'created_at' => '2020-06-29 07:48:07',
                'updated_at' => '2020-07-03 06:43:41',
            ],
            1 => [
                'id'         => 2,
                'name'       => '运营',
                'slug'       => 'operations',
                'created_at' => '2020-07-03 06:43:25',
                'updated_at' => '2020-07-03 06:43:25',
            ],
        ]);
    }
}
