<?php

namespace App\Installer;

use \Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Installer
{

    /**
     * Contains the installer steps in the correct order of execution
     * The slug must consist of (lowercase) a-z or dashes only!
     * @var array[]
     */
    public static array $installSteps = [
        [
            'name' => 'Database migrations',
            'description' => 'Creates tables and indexes in the database',
            'slug' => 'migrate-database',
            'running' => false,
            'status' => -1,
            'method' => 'migrateDatabase'
        ],
        [
            'name' => 'Database seeding',
            'description' => 'Adds initial data to the database',
            'slug' => 'seed-database',
            'running' => false,
            'status' => -1,
            'method' => 'seedDatabase'
        ],
        [
            'name' => 'Optimize install',
            'description' => 'Optimizes the app cache',
            'slug' => 'optimize-install',
            'running' => false,
            'status' => -1,
            'method' => 'optimizeInstall'
        ],
        [
            'name' => 'Finishing up',
            'description' => 'Finalize the installation',
            'slug' => 'finish-install',
            'running' => false,
            'status' => -1,
            'method' => 'finishInstall'
        ]
    ];


    /**
     * Tests & Configures the SQL settings to use
     * @param string $dbtype
     * @param string $host
     * @param string $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @return InstallStepResult
     */
    public function testSqlConnection(): InstallStepResult
    {
        $success = true;
        $exception = null;

        // Test database connection
        try {
            // Test the PDO connection
            DB::connection()->getPdo();

            # Clear the cache config
            Artisan::call('config:cache');
        } catch (\PDOException $e) {
            return new InstallStepResult(false, $e->getMessage(), $e);
        } catch (Exception $e) {
            $exception = $e;
            $success = false;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * Executes the artisan migrate command
     * @return InstallStepResult
     */
    public function migrateDatabase(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Execute the database migrations
            Artisan::call('migrate --force');
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * Executes the artisan db:seed command
     * @return InstallStepResult
     */
    public function seedDatabase(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Seed the database
            Artisan::call('db:seed --force');
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * Generates laravel cache for things like Routes & Views to speed up requests
     * @return InstallStepResult
     */
    public function optimizeInstall(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Generate route cache
            Artisan::call('route:cache');

            # Generate view cache
            Artisan::call('view:cache');
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * Finishes up the Shoutz0r installation by setting shoutzor.installed to true, and rebuilding the config cache
     * @return InstallStepResult
     */
    public function finishInstall(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Set installed to true
            Cache::put('shoutzor.installed', true);
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * rebuilds the config cache
     * @return InstallStepResult
     */
    public function rebuildConfigCache(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Execute the database migrations
            Artisan::call('config:cache');
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }

    /**
     * Runs the seeder that aids during development
     * @return InstallStepResult
     */
    public function developmentSeedDatabase(): InstallStepResult
    {
        $success = true;
        $exception = null;

        try {
            # Seed the database
            Artisan::call('db:seed --class=DevelopmentSeeder --force');
        } catch (Exception $e) {
            $success = false;
            $exception = $e;
        }

        return new InstallStepResult($success, $success ? Artisan::output() : $exception?->getMessage() ?? '', $exception);
    }
}
