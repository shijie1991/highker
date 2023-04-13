<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        DB::table('ad')->delete();

        DB::table('ad')->insert([
            0 => [
                'id' => 1,
                'name' => '广告 1',
                'category_id' => 1,
                'status' => 1,
                'image' => 'images/cf37e71aad747b799db78258780d312f.JPG',
                'url' => 'http://highker.applinzi.com/',
                'target' => 1,
                'before' => null,
                'after' => null,
                'created_at' => '2020-03-22 14:34:31',
                'updated_at' => '2020-03-22 17:20:43',
            ],
            1 => [
                'id' => 2,
                'name' => '广告 2',
                'category_id' => 3,
                'status' => 1,
                'image' => 'images/图片.jpg',
                'url' => 'http://highker.applinzi.com/admin',
                'target' => 1,
                'before' => '2020-03-01 00:00:00',
                'after' => '2020-03-31 00:00:00',
                'created_at' => '2020-03-22 14:59:12',
                'updated_at' => '2020-03-22 17:20:32',
            ],
        ]);
    }
}
