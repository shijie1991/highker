<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console\Cron;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'corn:test';

    protected $description = 'Test cronTab Run';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Test cronTab Run successfully.');
    }
}
