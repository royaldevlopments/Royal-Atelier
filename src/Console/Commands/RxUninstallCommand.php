<?php

namespace RoyalPanel\RoyalAtelier\Console\Commands;

use Illuminate\Console\Command;
use RoyalPanel\RoyalAtelier\Libraries\ExtensionLibrary;

class RxUninstallCommand extends Command
{
    protected $signature = 'rx:uninstall {id : Extension ID}';
    protected $description = 'Uninstall a Royal Extension';

    public function handle(ExtensionLibrary $library): int
    {
        $id = $this->argument('id');
        $result = $library->uninstall($id);

        if ($result['success']) {
            $this->info("Extension {$id} uninstalled");
            return Command::SUCCESS;
        }

        $this->error('Failed to uninstall extension');
        return Command::FAILURE;
    }
}
