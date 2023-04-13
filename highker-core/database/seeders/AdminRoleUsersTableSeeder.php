<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminRoleUsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_role_users')->delete();

        \DB::table('admin_role_users')->insert([
            0 => [
                'role_id'    => 1,
                'user_id'    => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'role_id'    => 2,
                'user_id'    => 2,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
