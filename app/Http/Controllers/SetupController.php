<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Exception;

class SetupController extends Controller
{
    /**
     * Show the setup page for storage mode selection
     */
    public function index()
    {
        $storageMode = config('tinyurl.storage_mode');

        // If already configured, redirect to main app
        if (!empty($storageMode)) {
            return redirect('/');
        }

        $storageModes = config('tinyurl.storage_modes');

        return view('setup', compact('storageModes'));
    }

    /**
     * Handle storage mode selection and setup
     */
    public function configure(Request $request)
    {
        // Validate the request
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'storage_mode' => 'required|in:single,multi_table,multi_db'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid storage mode selected',
                'errors' => $validator->errors()
            ], 422);
        }

        $storageMode = $request->storage_mode;

        try {
            // Update configuration file
            $this->updateConfigFile('storage_mode', $storageMode);

            // Configure database connections for multi-db mode
            if ($storageMode === 'multi_db') {
                $this->configureMultiDbConnections($request);
            }

            // Test database connection before running migrations
            $this->testDatabaseConnection($storageMode);

            // Run appropriate migrations
            $this->runMigrations($storageMode);

            // Clear all caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return response()->json([
                'success' => true,
                'message' => 'Storage mode configured successfully!',
                'redirect' => '/'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test database connection before running migrations
     */
    private function testDatabaseConnection($storageMode)
    {
        try {
            if ($storageMode === 'multi_db') {
                // Test multi-db connections
                $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];
                foreach ($connections as $connection) {
                    DB::connection($connection)->getPdo();
                }
            } else {
                // Test default connection
                DB::connection()->getPdo();
            }
        } catch (Exception $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage() . '. Please check your database configuration.');
        }
    }

    /**
     * Test database connections
     */
    public function testConnections(Request $request)
    {
        $storageMode = $request->storage_mode;
        $results = [];

        try {
            if ($storageMode === 'multi_db') {
                // Test multiple database connections
                $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];

                foreach ($connections as $connection) {
                    try {
                        // Try to create the connection configuration
                        $this->createDbConnection($connection, $request);

                        // Test the connection
                        DB::connection($connection)->getPdo();
                        $results[$connection] = [
                            'status' => 'success',
                            'message' => 'Connection successful'
                        ];
                    } catch (Exception $e) {
                        $results[$connection] = [
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ];
                    }
                }
            } else {
                // Test default connection
                try {
                    DB::connection()->getPdo();
                    $results['default'] = [
                        'status' => 'success',
                        'message' => 'Connection successful'
                    ];
                } catch (Exception $e) {
                    $results['default'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'connections' => $results
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update configuration file with new value
     */
    private function updateConfigFile($key, $value)
    {
        $configFile = config_path('tinyurl.php');

        if (!File::exists($configFile)) {
            throw new Exception('tinyurl.php config file not found');
        }

        $configContent = File::get($configFile);

        // Update the storage_mode value in the config file
        if ($key === 'storage_mode') {
            // Look for the current pattern in the config file
            $pattern = "/'storage_mode'\s*=>\s*'[^']*'/";
            $replacement = "'storage_mode' => '{$value}'";

            if (preg_match($pattern, $configContent)) {
                $configContent = preg_replace($pattern, $replacement, $configContent);
            } else {
                throw new Exception('Could not find storage_mode in config file to update');
            }
        }

        File::put($configFile, $configContent);

        // Clear config cache to reload the updated config
        Artisan::call('config:clear');
    }

    /**
     * Update database configuration in tinyurl config file
     */
    private function updateDatabaseConfig($connectionName, $connectionData)
    {
        $configFile = config_path('tinyurl.php');

        if (!File::exists($configFile)) {
            throw new Exception('tinyurl.php config file not found');
        }

        $configContent = File::get($configFile);

        // Create connection configuration string
        $connectionConfig = "        '{$connectionName}' => [
            'host' => '{$connectionData['host']}',
            'port' => '{$connectionData['port']}',
            'database' => '{$connectionData['database']}',
            'username' => '{$connectionData['username']}',
            'password' => '{$connectionData['password']}',
        ],";

        // Check if the connection already exists in the config
        $pattern = "/'{$connectionName}'\s*=>\s*\[[^\]]*\],/s";
        if (preg_match($pattern, $configContent)) {
            // Update existing connection
            $configContent = preg_replace($pattern, $connectionConfig, $configContent);
        } else {
            // Add new connection - find the database_connections array and add to it
            $dbConnectionsPattern = "/'database_connections'\s*=>\s*\[([^\]]*)\]/s";

            if (preg_match($dbConnectionsPattern, $configContent, $matches)) {
                $currentConnections = $matches[1];

                // Clean up any existing malformed entries and add the new connection
                $newConnections = trim($currentConnections) . "\n{$connectionConfig}";
                $replacement = "'database_connections' => [{$newConnections}\n    ]";

                $configContent = preg_replace($dbConnectionsPattern, $replacement, $configContent);
            } else {
                throw new Exception('Could not find database_connections section in config file');
            }
        }

        File::put($configFile, $configContent);

        // Clear config cache to reload the updated config
        Artisan::call('config:clear');
    }
    /**
     * Configure multiple database connections
     */
    private function configureMultiDbConnections($request)
    {
        $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];

        foreach ($connections as $index => $connection) {
            $this->createDbConnection($connection, $request, $index + 1);
        }
    }

    /**
     * Create database connection configuration
     */
    private function createDbConnection($connectionName, $request, $index = 1)
    {
        $host = $request->get("db_{$index}_host", '127.0.0.1');
        $port = $request->get("db_{$index}_port", '3306');
        $database = $request->get("db_{$index}_database", "tinyurl_{$index}");
        $username = $request->get("db_{$index}_username", 'root');
        $password = $request->get("db_{$index}_password", '');

        // Store database configurations in tinyurl config file
        $this->updateDatabaseConfig($connectionName, [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ]);

        // Configure the connection in runtime
        Config::set("database.connections.{$connectionName}", [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],
        ]);
    }

    /**
     * Run migrations based on storage mode
     */
    private function runMigrations($storageMode)
    {
        switch ($storageMode) {
            case 'single':
                // Force fresh start - drop all tables and run all migrations
                $this->forceCleanMigration();
                break;
            case 'multi_table':
                // Force fresh start - drop all tables and run all migrations
                $this->forceCleanMigration();
                break;

            case 'multi_db':
                // Run fresh migrations on each database connection
                $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];

                foreach ($connections as $connection) {
                    try {
                        // Force fresh start on each connection
                        $this->forceCleanMigration($connection);
                    } catch (Exception $e) {
                        throw new Exception("Failed to migrate on {$connection}: " . $e->getMessage());
                    }
                }
                break;

            default:
                throw new Exception('Invalid storage mode');
        }
    }

    /**
     * Force a completely clean migration by dropping all tables first
     */
    private function forceCleanMigration($connection = null)
    {
        try {
            // First, try to reset the database (drops all tables)
            if ($connection) {
                Artisan::call('db:wipe', [
                    '--database' => $connection,
                    '--force' => true
                ]);
            } else {
                Artisan::call('db:wipe', [
                    '--force' => true
                ]);
            }
        } catch (Exception $e) {
            // If db:wipe fails, try migrate:fresh
        }

        // Then run fresh migrations
        if ($connection) {
            Artisan::call('migrate:fresh', [
                '--database' => $connection,
                '--force' => true
            ]);
        } else {
            Artisan::call('migrate:fresh', [
                '--force' => true
            ]);
        }
    }

    /**
     * Check if setup is completed
     */
    public function status()
    {
        $storageMode = config('tinyurl.storage_mode');

        return response()->json([
            'configured' => !empty($storageMode),
            'storage_mode' => $storageMode,
            'storage_modes' => config('tinyurl.storage_modes'),
            'database_connections' => config('tinyurl.database_connections', [])
        ]);
    }

    /**
     * Show current configuration for debugging
     */
    public function showConfig()
    {
        $config = config('tinyurl');

        return response()->json([
            'tinyurl_config' => $config,
            'config_file_exists' => file_exists(config_path('tinyurl.php')),
            'config_writable' => is_writable(config_path('tinyurl.php'))
        ]);
    }
}