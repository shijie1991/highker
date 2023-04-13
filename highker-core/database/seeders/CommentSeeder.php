<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Database\Factories\CommentFactory;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run()
    {
        $count = 100;
        $this->command->getOutput()->progressStart($count);
        CommentFactory::new()->count($count)->create()->each(function () {
            $this->command->getOutput()->progressAdvance();
        });
        $this->command->getOutput()->progressFinish();
        $this->command->getOutput()->writeln('生成了 '.$count.' 条评论');
    }
}
