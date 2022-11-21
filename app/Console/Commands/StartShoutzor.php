<?php

namespace App\Console\Commands;

use \Exception;
use App\Exceptions\ShoutzorInstallerException;
use App\Exceptions\FormValidationException;
use App\HealthCheck\HealthCheckManager;
use App\Installer\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * Wrapper function for octane:start
 * Docker containers can be finicky with config caches.
 */
class StartShoutzor extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'shoutzor:start {--watch} {--host=0.0.0.0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts Shoutz0r';

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
        $this->line('Shoutz0r');

        $this->installer = new Installer();
        $this->installer->optimizeInstall();

        # Start the server
        Artisan::call('octane:start', [
            '--watch' => $this->option('watch'),
            '--host' => $this->option('host')
        ]);
    }
}
