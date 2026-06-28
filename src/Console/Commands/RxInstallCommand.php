<?php

namespace RoyalPanel\RoyalAtelier\Console\Commands;

use Illuminate\Console\Command;
use RoyalPanel\RoyalAtelier\Libraries\ExtensionLibrary;

class RxInstallCommand extends Command
{
    protected $signature = 'rx:install {package : Path to .blueprint package file}';
    protected $description = 'Install a Royal Extension package';

    public function handle(ExtensionLibrary $library): int
    {
        $package = $this->argument('package');

        if (!file_exists($package)) {
            $this->error("Package file not found: {$package}");
            return Command::FAILURE;
        }

        $result = $library->install($package);

        if ($result['success']) {
            $this->info("Extension {$result['id']} installed successfully");
            return Command::SUCCESS;
        }

        $this->error($result['error']);
        return Command::FAILURE;
    }
}
