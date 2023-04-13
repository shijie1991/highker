<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminPermissionsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_permissions')->delete();

        \DB::table('admin_permissions')->insert([
            0 => [
                'id'          => 1,
                'name'        => '系统管理',
                'slug'        => 'auth-management',
                'http_method' => '',
                'http_path'   => '',
                'order'       => 1,
                'parent_id'   => 0,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:45:51',
            ],
            1 => [
                'id'          => 2,
                'name'        => '管理员',
                'slug'        => 'users',
                'http_method' => '',
                'http_path'   => '/auth/users*',
                'order'       => 2,
                'parent_id'   => 1,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:46:14',
            ],
            2 => [
                'id'          => 3,
                'name'        => '角色管理',
                'slug'        => 'roles',
                'http_method' => '',
                'http_path'   => '/auth/roles*',
                'order'       => 3,
                'parent_id'   => 1,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:46:44',
            ],
            3 => [
                'id'          => 4,
                'name'        => '权限管理',
                'slug'        => 'permissions',
                'http_method' => '',
                'http_path'   => '/auth/permissions*',
                'order'       => 4,
                'parent_id'   => 1,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:47:06',
            ],
            4 => [
                'id'          => 5,
                'name'        => '菜单管理',
                'slug'        => 'menu',
                'http_method' => '',
                'http_path'   => '/auth/menu*',
                'order'       => 5,
                'parent_id'   => 1,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:47:16',
            ],
            5 => [
                'id'          => 6,
                'name'        => '操作日志管理',
                'slug'        => 'operation-log',
                'http_method' => '',
                'http_path'   => '/auth/logs*',
                'order'       => 6,
                'parent_id'   => 1,
                'created_at'  => '2020-06-29 07:48:07',
                'updated_at'  => '2020-07-03 06:47:29',
            ],
            6 => [
                'id'          => 7,
                'name'        => '违禁词管理',
                'slug'        => 'keyword_shied',
                'http_method' => '',
                'http_path'   => 'sensitive_word*',
                'order'       => 7,
                'parent_id'   => 1,
                'created_at'  => '2020-07-03 06:47:56',
                'updated_at'  => '2020-07-03 06:58:05',
            ],
        ]);
    }
}
