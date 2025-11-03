<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\UrlStorageManager;
use App\Models\Urls;

class ManageUrlStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tinyurl:storage 
                            {action : The action to perform (stats|cleanup|migrate)}
                            {--mode= : Storage mode to use}
                            {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage TinyURL storage operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'stats':
                $this->showStats();
                break;
            case 'cleanup':
                $this->cleanupExpiredUrls();
                break;
            case 'migrate':
                $this->migrateStorage();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: stats, cleanup, migrate");
                return 1;
        }

        return 0;
    }

    private function showStats()
    {
        $this->info('TinyURL Storage Statistics');
        $this->info('========================');

        $stats = Urls::getStats();
        $storageMode = UrlStorageManager::getStorageMode();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Storage Mode', $storageMode],
                ['Total URLs', number_format($stats['total_urls'])],
                ['Active URLs', number_format($stats['active_urls'])],
                ['Expired URLs', number_format($stats['expired_urls'])],
            ]
        );

        if ($storageMode === 'multi_db') {
            $this->info("\nDatabase Distribution:");
            $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];
            foreach ($connections as $connection) {
                try {
                    $count = DB::connection($connection)->table('urls')->count();
                    $this->line("  {$connection}: " . number_format($count) . " URLs");
                } catch (\Exception $e) {
                    $this->line("  {$connection}: Connection failed");
                }
            }
        } elseif ($storageMode === 'multi_table') {
            $this->info("\nTable Distribution:");
            $tables = ['urls_a_f', 'urls_g_l', 'urls_m_r', 'urls_s_z'];
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $this->line("  {$table}: " . number_format($count) . " URLs");
                } catch (\Exception $e) {
                    $this->line("  {$table}: Table not found");
                }
            }
        }
    }

    private function cleanupExpiredUrls()
    {
        $this->info('Cleaning up expired URLs...');

        if (!$this->option('force') && !$this->confirm('This will permanently delete all expired URLs. Continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $storageMode = UrlStorageManager::getStorageMode();
        $deletedCount = 0;

        try {
            if ($storageMode === 'multi_db') {
                $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];
                foreach ($connections as $connection) {
                    try {
                        $deleted = DB::connection($connection)
                            ->table('urls')
                            ->where('expired_at', '<', now())
                            ->delete();
                        $deletedCount += $deleted;
                        $this->line("  {$connection}: Deleted {$deleted} URLs");
                    } catch (\Exception $e) {
                        $this->error("  {$connection}: " . $e->getMessage());
                    }
                }
            } elseif ($storageMode === 'multi_table') {
                $tables = ['urls_a_f', 'urls_g_l', 'urls_m_r', 'urls_s_z'];
                foreach ($tables as $table) {
                    try {
                        $deleted = DB::table($table)
                            ->where('expired_at', '<', now())
                            ->delete();
                        $deletedCount += $deleted;
                        $this->line("  {$table}: Deleted {$deleted} URLs");
                    } catch (\Exception $e) {
                        $this->error("  {$table}: " . $e->getMessage());
                    }
                }
            } else {
                $deletedCount = DB::table('urls')
                    ->where('expired_at', '<', now())
                    ->delete();
                $this->line("  urls: Deleted {$deletedCount} URLs");
            }

            $this->info("Total expired URLs deleted: " . number_format($deletedCount));
        } catch (\Exception $e) {
            $this->error("Error during cleanup: " . $e->getMessage());
        }
    }

    private function migrateStorage()
    {
        $currentMode = UrlStorageManager::getStorageMode();
        $newMode = $this->option('mode');

        if (!$newMode) {
            $newMode = $this->choice(
                'Select the target storage mode:',
                ['single', 'multi_table', 'multi_db'],
                0
            );
        }

        if ($currentMode === $newMode) {
            $this->info("Already using {$currentMode} storage mode.");
            return;
        }

        $this->info("Migrating from {$currentMode} to {$newMode}...");

        if (!$this->option('force') && !$this->confirm('This operation may take a long time and should be done during maintenance. Continue?')) {
            $this->info('Migration cancelled.');
            return;
        }

        // This is a complex operation that would require careful implementation
        // For now, we'll just show a message
        $this->warn('Storage migration is not implemented yet.');
        $this->info('To change storage mode:');
        $this->info('1. Update STORAGE_MODE in .env file');
        $this->info('2. Run migrations: php artisan migrate');
        $this->info('3. Manually migrate data if needed');
    }
}
