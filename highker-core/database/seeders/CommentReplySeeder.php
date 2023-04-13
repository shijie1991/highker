<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace Database\Seeders;

use Database\Factories\CommentReplyFactory;
use Illuminate\Database\Seeder;

class CommentReplySeeder extends Seeder
{
    public function run()
    {
        $count = 100;
        $this->command->getOutput()->progressStart($count);
        CommentReplyFactory::new()->count($count)->create()->each(function () {
            $this->command->getOutput()->progressAdvance();
        });
        $this->command->getOutput()->progressFinish();
        $this->command->getOutput()->writeln('生成了 '.$count.' 条一级回复');
    }
}
