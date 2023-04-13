<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'highker:install {--re}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the highker package --re re-install ';

    /**
     * Install directory.
     */
    protected string $directory = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->initDirectory();

        $this->initDatabase();
    }

    /**
     * Create tables and seed it.
     */
    public function initDatabase()
    {
        $force = $this->option('re');
        $options = [];
        $options['--database'] = env('DB_CONNECTION');
        $options['--path'] = 'database/migrations';

        if ($force) {
            $this->call('migrate:fresh', $options);
            $this->line('<info>ResetHighKerCoreDatabase! </info>'.$options['--path']);
            $this->call('migrate', $options);
        }

        $this->call('highker:seed');

        $this->line('<info>init highker database</info>');
    }

    /**
     * Initialize the directory.
     */
    protected function initDirectory()
    {
        $force = $this->option('re');
        $options = [];
        if ($force) {
            $options['--force'] = true;
        }
        $this->call('highker:publish', $options);
    }

    /**
     * Get stub contents.
     *
     * @param $name
     */
    protected function getStub($name): string
    {
        return $this->laravel['files']->get(__DIR__."/stubs/{$name}.stub");
    }

    /**
     * Make new directory.
     */
    protected function makeDir(string $path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/{$path}", 0755, true, true);
    }
}
