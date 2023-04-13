<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console;

use Illuminate\Console\Command;

class UninstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'highker:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the highker package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Are you sure to uninstall highker-core?')) {
            return;
        }

        $this->removeFilesAndDirectories();

        $this->line('<info>Uninstalling HighKer-Core!</info>');
    }

    /**
     * Remove files and directories.
     */
    protected function removeFilesAndDirectories()
    {
        $this->laravel['files']->delete(database_path('migrations'));
        $this->laravel['files']->delete(database_path('seeders'));
        $this->laravel['files']->delete(config_path('core.php'));
    }
}
