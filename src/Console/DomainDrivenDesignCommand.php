<?php

namespace Octopy\DDD\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Octopy\DDD\Installer\Installer;

class DomainDrivenDesignCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ddd';

    /**
     * @var string
     */
    protected $description = 'Domain Driven Design';

    /**
     * @param  Application $app
     * @param  Installer   $installer
     * @return void
     */
    public function handle(Application $app, Installer $installer) : void
    {
        if (! is_dir($app->path('Infrastructure/Core'))) {
            if ($installer->install()) {
                $this->info('DDD installed successfully.');
            }
        } else {
            $choice = $this->confirm('It looks like DDD is installed, do you want to revert to Laravel structure ?', false);

            if ($choice) {
                if ($installer->restoreOriginalStructure()) {
                    $this->warn('DDD structure deleted successfully and reverted to Laravel structure.');
                }
            }
        }
    }
}
