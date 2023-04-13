<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_users')->delete();

        \DB::table('admin_users')->insert([
            0 => [
                'id'             => 1,
                'username'       => 'admin',
                'password'       => '$2y$10$1W2gRjIFK/Hhw/O7vIdW2.qU3rlT1qNcGxBUs9AJO4hFltoK9FN0.',
                'name'           => '管理员',
                'avatar'         => 'images/d70722ec2888f48e2040561c192d2a19.png',
                'remember_token' => 'zrpPNAGDghIRI96dCqkYUSqNZqB7jH4kc42Bovd3Uc4xxpK0ABafFTqAwWs2',
                'created_at'     => '2020-06-29 07:48:07',
                'updated_at'     => '2023-03-17 14:45:16',
            ],
            1 => [
                'id'             => 2,
                'username'       => 'shijie',
                'password'       => '$2y$10$Cj8WCLrb.1Ohrxu.ch1rreDm/C4TkRyl.mRKwVqiz.9N1z.x1UGBS',
                'name'           => '史杰',
                'avatar'         => null,
                'remember_token' => null,
                'created_at'     => '2020-07-03 06:41:14',
                'updated_at'     => '2020-07-03 06:41:14',
            ],
        ]);
    }
}
