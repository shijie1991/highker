<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SeedCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'highker:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the highker test data';

    /**
     * Install directory.
     */
    protected string $directory = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('<info>Seed HighKer Test Data!</info>');

        $process = new Process(['composer', 'dump-autoload']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $options = [];
        $options['--path'] = 'database/migrations';
        $options['--database'] = env('DB_CONNECTION');
        $options['--seeder'] = '\Database\Seeders\DatabaseSeeder';
        $this->call('migrate:fresh', $options);
        // 执行其他需要 迁移的项目
        $this->call('migrate');

        $this->line('<info>HighKer Success!</info>');
    }
}
