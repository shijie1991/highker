<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdCategoryTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        DB::table('ad_category')->delete();

        DB::table('ad_category')->insert([
            0 => [
                'id' => 1,
                'name' => '广告位 1',
                'width' => '100',
                'height' => '100',
                'status' => 1,
                'created_at' => '2020-03-22 13:06:49',
                'updated_at' => '2020-03-22 13:06:58',
            ],
            1 => [
                'id' => 2,
                'name' => '评论列表页 广告',
                'width' => '100',
                'height' => '150',
                'status' => 0,
                'created_at' => '2020-03-22 13:14:36',
                'updated_at' => '2020-03-22 13:14:36',
            ],
            2 => [
                'id' => 3,
                'name' => '动态广告位',
                'width' => '15',
                'height' => '30',
                'status' => 1,
                'created_at' => '2020-03-22 13:16:59',
                'updated_at' => '2020-03-22 13:17:08',
            ],
        ]);
    }
}
