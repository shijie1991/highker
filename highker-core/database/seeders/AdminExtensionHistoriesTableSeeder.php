<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminExtensionHistoriesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        \DB::table('admin_extension_histories')->delete();

        \DB::table('admin_extension_histories')->insert([
            0 => [
                'id'         => 1,
                'name'       => 'abovesky.dcat-media-player',
                'type'       => 1,
                'version'    => '1.0.0',
                'detail'     => 'Initialize extension.',
                'created_at' => '2023-03-17 14:44:36',
                'updated_at' => '2023-03-17 14:44:36',
            ],
        ]);
    }
}
