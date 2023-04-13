<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Admin Seeder
        $this->call(AdminExtensionHistoriesTableSeeder::class);
        $this->call(AdminExtensionsTableSeeder::class);
        $this->call(AdminMenuTableSeeder::class);
        $this->call(AdminPermissionMenuTableSeeder::class);
        $this->call(AdminPermissionsTableSeeder::class);
        $this->call(AdminRoleMenuTableSeeder::class);
        $this->call(AdminRolePermissionsTableSeeder::class);
        $this->call(AdminRolesTableSeeder::class);
        $this->call(AdminRoleUsersTableSeeder::class);
        $this->call(AdminSettingsTableSeeder::class);
        $this->call(AdminUsersTableSeeder::class);

        $this->call(KeywordShieldTableSeeder::class);

        $this->call(ArticleCategoryTableSeeder::class);
        $this->call(ArticleTableSeeder::class);

        // $this->call(UserSeeder::class);
        // $this->call(AdCategoryTableSeeder::class);
        // $this->call(AdTableSeeder::class);
        // $this->call(FeedTableSeeder::class);
        // $this->call(FeedContentTableSeeder::class);
        // $this->call(FeedImageTableSeeder::class);

        // $this->call(TopicTableSeeder::class);
        // $this->call(TopicGroupTableSeeder::class);
        // 评论
        // $this->call(CommentSeeder::class);
        // 一级回复
        // $this->call(CommentReplySeeder::class);
        // 二级回复
        // $this->call(CommentSecondReplySeeder::class);
    }
}
