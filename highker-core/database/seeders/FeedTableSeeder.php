<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FeedTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('feed')->delete();

        \DB::table('feed')->insert([
            0 => [
                'id'      => 1,
                'user_id' => 23,
                'status'  => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:24:53',
                'updated_at'    => '2020-08-05 14:24:53',
            ],
            1 => [
                'id'      => 2,
                'user_id' => 24,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:14',
                'updated_at'    => '2020-08-05 14:25:14',
            ],
            2 => [
                'id'      => 3,
                'user_id' => 25,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:34',
                'updated_at'    => '2020-08-05 14:25:34',
            ],
            3 => [
                'id'      => 4,
                'user_id' => 26,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:40',
                'updated_at'    => '2020-08-05 14:25:40',
            ],
            4 => [
                'id'      => 5,
                'user_id' => 27,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:45',
                'updated_at'    => '2020-08-05 14:25:45',
            ],
            5 => [
                'id'      => 6,
                'user_id' => 28,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:49',
                'updated_at'    => '2020-08-05 14:25:49',
            ],
            6 => [
                'id'      => 7,
                'user_id' => 29,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:25:55',
                'updated_at'    => '2020-08-05 14:25:55',
            ],
            7 => [
                'id'      => 8,
                'user_id' => 30,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:00',
                'updated_at'    => '2020-08-05 14:26:00',
            ],
            8 => [
                'id'      => 9,
                'user_id' => 31,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:04',
                'updated_at'    => '2020-08-05 14:26:04',
            ],
            9 => [
                'id'      => 10,
                'user_id' => 32,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:09',
                'updated_at'    => '2020-08-05 14:26:09',
            ],
            10 => [
                'id'      => 11,
                'user_id' => 33,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:13',
                'updated_at'    => '2020-08-05 14:26:13',
            ],
            11 => [
                'id'      => 12,
                'user_id' => 34,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:18',
                'updated_at'    => '2020-08-05 14:26:18',
            ],
            12 => [
                'id'      => 13,
                'user_id' => 35,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:22',
                'updated_at'    => '2020-08-05 14:26:22',
            ],
            13 => [
                'id'      => 14,
                'user_id' => 36,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:28',
                'updated_at'    => '2020-08-05 14:26:28',
            ],
            14 => [
                'id'      => 15,
                'user_id' => 37,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:33',
                'updated_at'    => '2020-08-05 14:26:33',
            ],
            15 => [
                'id'      => 16,
                'user_id' => 38,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:38',
                'updated_at'    => '2020-08-05 14:26:38',
            ],
            16 => [
                'id'      => 17,
                'user_id' => 39,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:42',
                'updated_at'    => '2020-08-05 14:26:42',
            ],
            17 => [
                'id'      => 18,
                'user_id' => 40,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:48',
                'updated_at'    => '2020-08-05 14:26:48',
            ],
            18 => [
                'id'      => 19,
                'user_id' => 41,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:26:52',
                'updated_at'    => '2020-08-05 14:26:52',
            ],
            19 => [
                'id'      => 20,
                'user_id' => 42,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:02',
                'updated_at'    => '2020-08-05 14:27:02',
            ],
            20 => [
                'id'      => 21,
                'user_id' => 43,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:08',
                'updated_at'    => '2020-08-05 14:27:08',
            ],
            21 => [
                'id'      => 22,
                'user_id' => 44,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:12',
                'updated_at'    => '2020-08-05 14:27:12',
            ],
            22 => [
                'id'      => 23,
                'user_id' => 45,

                'status' => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:17',
                'updated_at'    => '2020-08-05 14:27:17',
            ],
            23 => [
                'id'      => 24,
                'user_id' => 46,
                'status'  => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:24',
                'updated_at'    => '2020-08-05 14:27:24',
            ],
            24 => [
                'id'      => 25,
                'user_id' => 47,
                'status'  => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:30',
                'updated_at'    => '2020-08-05 14:27:30',
            ],
            25 => [
                'id'      => 26,
                'user_id' => 48,
                'status'  => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:38',
                'updated_at'    => '2020-08-05 14:27:38',
            ],
            26 => [
                'id'      => 27,
                'user_id' => 49,
                'status'  => 1,

                'location'      => null,
                'view_count'    => 0,
                'like_count'    => 0,
                'comment_count' => 0,
                'created_at'    => '2020-08-05 14:27:44',
                'updated_at'    => '2020-08-05 14:27:44',
            ],
        ]);
    }
}
