<?php

namespace App\Console\Commands;

use App\Installer\Installer;
use Illuminate\Support\Facades\Artisan;
use Laravel\Octane\Commands\StartCommand as OctaneStartCommand;

/**
 * Wrapper function for octane:start
 * Docker containers can be finicky with config caches.
 *
 * This function is only needed for development environments where the
 * backend docker container will be watching for changes.
 */
class StartShoutzor extends OctaneStartCommand
{
    /**
     * The console command description.
     *
     * @var string
     */
    public $description = 'Starts the octane server but resets the config cache first';

    /**
     * Instance of the Installer class
     * @var Installer
     */
    private Installer $installer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Runs the shoutzor install command.
     * This will execute the same steps as the graphical installation wizard.
     * @return int
     */
    public function handle()
    {
        $this->line('Starting Shoutz0r');

        $this->line('Clearing config cache');
        Artisan::call('config:clear');

        $this->line('Starting the Octane Swoole server');
        return $this->startSwooleServer();
    }
}
