<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'highker:publish {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-publish highker-core assets, configuration, language and migration files. If you want overwrite the existing files, you can add the `--force` option';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $options = ['--provider' => 'HighKer\Core\Providers\HighKerCoreServiceProvider'];
        if ($force === true) {
            $options['--force'] = true;
        }
        $this->call('vendor:publish', $options);
        // $this->call('view:clear');
    }
}
