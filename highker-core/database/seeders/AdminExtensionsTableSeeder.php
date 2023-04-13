<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminExtensionsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_extensions')->delete();

        \DB::table('admin_extensions')->insert([
            0 => [
                'id'         => 1,
                'name'       => 'abovesky.dcat-media-player',
                'version'    => '1.0.0',
                'is_enabled' => 1,
                'options'    => null,
                'created_at' => '2023-03-17 14:44:36',
                'updated_at' => '2023-03-17 14:44:39',
            ],
        ]);
    }
}
