<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TopicGroupTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('topic_group')->delete();

        \DB::table('topic_group')->insert([
            0 => [
                'id'         => 1,
                'name'       => '我的关注',
                'parent_id'  => 0,
                'order'      => 0,
                'created_at' => '2022-04-24 20:13:58',
                'updated_at' => '2022-04-24 20:13:58',
            ],
            1 => [
                'id'         => 2,
                'name'       => '推荐话题',
                'parent_id'  => 0,
                'order'      => 0,
                'created_at' => '2022-04-24 20:14:16',
                'updated_at' => '2022-04-24 20:14:16',
            ],
            2 => [
                'id'         => 3,
                'name'       => '分组 1',
                'parent_id'  => 0,
                'order'      => 0,
                'created_at' => '2022-04-24 20:14:20',
                'updated_at' => '2022-04-24 20:14:20',
            ],
            3 => [
                'id'         => 4,
                'name'       => '分组 2',
                'parent_id'  => 0,
                'order'      => 0,
                'created_at' => '2022-04-24 20:14:23',
                'updated_at' => '2022-04-24 20:14:23',
            ],
        ]);
    }
}
