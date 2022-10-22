<?php

namespace App\Console\Commands;

use \Exception;
use App\Exceptions\ShoutzorInstallerException;
use App\Exceptions\FormValidationException;
use App\HealthCheck\HealthCheckManager;
use App\Installer\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * An Artisan command allowing for command-line installation of Shoutzor.
 * The Functionality of this installer is identical to the graphical installer of Shoutzor.
 */
class InstallShoutzor extends Command
{
    /**
     * The name and signature of the console command.
     * --useenv indicates that the existing .env file should be used during installation.
     * --dev indicates that this is a development environment and will populate the database with dummy data
     * @var string
     */
    protected $signature = 'shoutzor:install {--dev}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs Shoutz0r via the command line';

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

        $this->installer = new Installer();
    }

    /**
     * Runs the shoutzor install command.
     * This will execute the same steps as the graphical installation wizard.
     * @return int
     */
    public function handle()
    {
        $this->line('Shoutz0r CLI Installer');

        try {
            // Running the installer while shoutzor is already installed will break & reset things. Bad idea.
            if (Cache::get('shoutzor.installed', false) === true) {
                throw new ShoutzorInstallerException('Shoutz0r is already installed, aborting.');
            }

            $this->checkHealth();
            $this->performInstall($this->option('dev'));
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Runs the healthchecks and will perform an auto-fix if any issues are detected
     * @throws Exception
     */
    private function checkHealth()
    {
        $this->info('Performing installation health-check..');

        $checks = app(HealthCheckManager::class)->getHealthStatus(true);

        // Keep track if any of the healthchecks are unhealthy
        $healthy = true;

        // Iterate over every healthcheck
        foreach ($checks as $check) {
            //Print the name & description of the healthcheck
            $this->line('[HealthCheck] ' . $check['name'] . ' - ' . $check['description']);

            // If unhealthy, print the reason
            if ($check['healthy'] === false) {
                $this->error($check['status']);
                $healthy = false;
            }
        }

        // Check if any of the healthchecks returned an unhealthy status
        if ($healthy === false) {
            $this->info('Found unhealthy healthchecks, performing auto-fix');

            // Perform the auto-fix
            $result = app(HealthCheckManager::class)->performAutoFix(true);

            // Print the results of the auto-fix
            foreach ($result['data'] as $fix) {
                $this->line('[HealthCheck] ' . $fix['name'] . ' Auto-fix result:');
                $this->line($fix['result']);
            }

            // Check if any of the health-checks still failed
            if ($result['result'] === false) {
                throw new ShoutzorInstallerException('Auto-fix failed on one or more healtchecks, manual fix required');
            } else {
                $this->info('Auto-fix succeeded in fixing the issues.');
            }
        }

        $this->info('All healthchecks are healthy!');
    }

    /**
     * Performs the actual installation of Shoutzor
     * @throws Exception
     */
    private function performInstall($isDev)
    {
        $this->info('Starting installation');
        $this->loadEnvFile();

        // Test the database connection
        $this->testDbLogin();

        // Retrieve the installation steps from the installer
        $installationSteps = Installer::$installSteps;

        // Run each installation step in-order
        foreach ($installationSteps as $step) {
            $this->info("Executing installation step '" . $step['name'] . "': " . $step['description']);
            // Dynamic method, the method names are in the array
            $stepResult = $this->installer->{$step['method']}();

            if ($stepResult->succeeded() === false) {
                throw new ShoutzorInstallerException("Installation step failed. Reason: " . $stepResult->getOutput());
            }
        }

        if ($isDev) {
            $this->info('Seeding database with the DevelopmentSeeder');
            $this->installer->developmentSeedDatabase();
            if ($stepResult->succeeded() === false) {
                throw new ShoutzorInstallerException("Installation step failed. Reason: " . $stepResult->getOutput());
            }
        }

        $this->info('Installation finished!');
    }

    private function loadEnvFile() {
        // Check if the .env file actually exists
        if (file_exists(base_path('.env')) === false) {
            throw new ShoutzorInstallerException('.env file not found in application root! Exiting.');
        }

        // Rebuild config cache
        $step = $this->installer->rebuildConfigCache();

        // Check if rebuilding the config cache worked
        if ($step->succeeded() === false) {
            throw new ShoutzorInstallerException('Failed to rebuild the config cache, reason: ' . $step->getOutput());
        }
    }

    private function testDbLogin()
    {
        $step = $this->installer->testSqlConnection();

        //Check if the SQL configuration is valid
        if ($step->succeeded()) {
            $this->info("SQL Login success!");
            return true;
        } else {
            // Check if it's a formValidation exception, or regular exception
            if ($step->getException() instanceof FormValidationException) {
                // $errors will now contain formValidationFieldError[] from the exception
                $errors = $step->getException()->getErrors();

                // Convert the array of formValidationFieldError objects into an array
                foreach ($errors as $e) {
                    $this->error($e->getField() . ": " . $e->getMessage());
                }
            } else {
                // Configuration failed, display error and restart the loop
                $this->error("SQL Login failed, reason: " . $step->getOutput());
            }

            return false;
        }
    }
}
