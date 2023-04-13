<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ArticleCategoryTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('article_category')->delete();

        \DB::table('article_category')->insert([
            0 => [
                'id'           => 1,
                'name'         => '系统文章',
                'parent_id'    => 0,
                'order'        => 0,
                'is_directory' => 0,
                'level'        => 0,
                'cate_path'    => '-',
                'created_at'   => '2022-04-20 21:24:09',
                'updated_at'   => '2022-04-20 21:24:09',
            ],
            1 => [
                'id'           => 2,
                'name'         => '用户协议',
                'parent_id'    => 1,
                'order'        => 0,
                'is_directory' => 1,
                'level'        => 1,
                'cate_path'    => '-1-',
                'created_at'   => '2022-05-09 19:58:41',
                'updated_at'   => '2022-05-09 19:58:41',
            ],
            2 => [
                'id'           => 3,
                'name'         => '常见问题',
                'parent_id'    => 1,
                'order'        => 0,
                'is_directory' => 1,
                'level'        => 1,
                'cate_path'    => '-1-',
                'created_at'   => '2022-05-09 19:58:59',
                'updated_at'   => '2022-05-09 19:58:59',
            ],
            3 => [
                'id'           => 4,
                'name'         => '账号问题',
                'parent_id'    => 3,
                'order'        => 0,
                'is_directory' => 1,
                'level'        => 2,
                'cate_path'    => '-1-3-',
                'created_at'   => '2022-05-09 20:20:26',
                'updated_at'   => '2023-03-21 03:10:33',
            ],
            4 => [
                'id'           => 5,
                'name'         => '产品使用',
                'parent_id'    => 3,
                'order'        => 0,
                'is_directory' => 1,
                'level'        => 2,
                'cate_path'    => '-1-3-',
                'created_at'   => '2022-05-09 20:20:35',
                'updated_at'   => '2023-03-21 03:10:43',
            ],
        ]);
    }
}
