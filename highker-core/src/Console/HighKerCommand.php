<?php

/*
 *  User: dirty
 *  Email: shijie1991@gmail.com
 */

namespace HighKer\Core\Console;

use HighKer\Core\Support\HighKer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HighKerCommand extends Command
{
    public static string $logo = <<<'LOGO'

        ██╗  ██╗██╗ ██████╗ ██╗  ██╗██╗  ██╗███████╗██████╗
        ██║  ██║██║██╔════╝ ██║  ██║██║ ██╔╝██╔════╝██╔══██╗
        ███████║██║██║  ███╗███████║█████╔╝ █████╗  ██████╔╝
        ██╔══██║██║██║   ██║██╔══██║██╔═██╗ ██╔══╝  ██╔══██╗
        ██║  ██║██║╚██████╔╝██║  ██║██║  ██╗███████╗██║  ██║
        ╚═╝  ╚═╝╚═╝ ╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝

        LOGO;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'highker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all highker commands';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line(static::$logo);
        $this->line(HighKer::getLongVersion());

        $this->comment('');
        $this->comment('Available commands:');

        $this->listCommands();
    }

    /**
     * @param $string
     *
     * @return int
     */
    public static function strlen($string)
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    /**
     * List all commands.
     */
    protected function listCommands()
    {
        $commands = collect(Artisan::all())->mapWithKeys(function ($command, $key) {
            if (Str::startsWith($key, 'highker:')) {
                return [$key => $command];
            }

            return [];
        })->toArray();

        $width = $this->getColumnWidth($commands);

        /** @var Command $command */
        foreach ($commands as $command) {
            $this->line(sprintf(" %-{$width}s %s", $command->getName(), $command->getDescription()));
        }
    }

    /**
     * @return int|mixed
     */
    private function getColumnWidth(array $commands)
    {
        $widths = [];

        foreach ($commands as $command) {
            $widths[] = static::strlen($command->getName());
            foreach ($command->getAliases() as $alias) {
                $widths[] = static::strlen($alias);
            }
        }

        return $widths ? max($widths) + 2 : 0;
    }
}
