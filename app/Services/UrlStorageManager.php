<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UrlStorageManager
{
    public static function getStorageMode(): string
    {
        return config('app.storage_mode', env('STORAGE_MODE', 'single'));
    }

    public static function getStorageTarget(string $token): string
    {
        $first = strtoupper($token[0]);
        $mode = self::getStorageMode();

        if ($mode === 'multi_db') {
            return match (true) {
                $first < 'G' => 'mysql_1',
                $first < 'M' => 'mysql_2',
                $first < 'S' => 'mysql_3',
                default => 'mysql_4',
            };
        }

        if ($mode === 'multi_table') {
            return match (true) {
                $first < 'G' => 'urls_a_f',
                $first < 'M' => 'urls_g_l',
                $first < 'S' => 'urls_m_r',
                default => 'urls_s_z',
            };
        }

        return 'urls'; // single table mode
    }

    public static function storeUrl(array $data): bool
    {
        try {
            $token = $data['token'];
            $mode = self::getStorageMode();

            if ($mode === 'multi_db') {
                $connection = self::getStorageTarget($token);
                return DB::connection($connection)->table('urls')->insert($data);
            } else {
                $table = self::getStorageTarget($token);
                return DB::table($table)->insert($data);
            }
        } catch (\Exception $e) {
            Log::error('Failed to store URL: ' . $e->getMessage());
            return false;
        }
    }

    public static function findUrl(string $token): ?object
    {
        try {
            $mode = self::getStorageMode();
            $cacheKey = "url_token_{$token}";

            // Try to get from cache first
            $cachedUrl = Cache::get($cacheKey);
            if ($cachedUrl) {
                return (object) $cachedUrl;
            }

            $url = null;

            if ($mode === 'multi_db') {
                $connection = self::getStorageTarget($token);
                $url = DB::connection($connection)
                    ->table('urls')
                    ->where('token', $token)
                    ->where('expired_at', '>', Carbon::now())
                    ->first();
            } else {
                $table = self::getStorageTarget($token);
                $url = DB::table($table)
                    ->where('token', $token)
                    ->where('expired_at', '>', Carbon::now())
                    ->first();
            }

            // Cache the result for 1 hour
            if ($url) {
                Cache::put($cacheKey, $url, 3600);
            }

            return $url;
        } catch (\Exception $e) {
            Log::error('Failed to find URL: ' . $e->getMessage());
            return null;
        }
    }

    public static function updateUrl(string $token, array $data): bool
    {
        try {
            $mode = self::getStorageMode();
            $cacheKey = "url_token_{$token}";

            // Clear cache
            Cache::forget($cacheKey);

            if ($mode === 'multi_db') {
                $connection = self::getStorageTarget($token);
                return DB::connection($connection)
                    ->table('urls')
                    ->where('token', $token)
                    ->update($data);
            } else {
                $table = self::getStorageTarget($token);
                return DB::table($table)
                    ->where('token', $token)
                    ->update($data);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update URL: ' . $e->getMessage());
            return false;
        }
    }

    public static function deleteUrl(string $token): bool
    {
        try {
            $mode = self::getStorageMode();
            $cacheKey = "url_token_{$token}";

            // Clear cache
            Cache::forget($cacheKey);

            if ($mode === 'multi_db') {
                $connection = self::getStorageTarget($token);
                return DB::connection($connection)
                    ->table('urls')
                    ->where('token', $token)
                    ->delete();
            } else {
                $table = self::getStorageTarget($token);
                return DB::table($table)
                    ->where('token', $token)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete URL: ' . $e->getMessage());
            return false;
        }
    }

    public static function generateUniqueToken(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $token = self::generateRandomToken();
            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new \Exception('Could not generate unique token after ' . $maxAttempts . ' attempts');
            }
        } while (self::tokenExists($token));

        return $token;
    }

    private static function generateRandomToken(): string
    {
        // Generate alphanumeric token (letters and numbers)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $token = '';

        for ($i = 0; $i < 8; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    private static function tokenExists(string $token): bool
    {
        try {
            $mode = self::getStorageMode();

            if ($mode === 'multi_db') {
                $connection = self::getStorageTarget($token);
                return DB::connection($connection)
                    ->table('urls')
                    ->where('token', $token)
                    ->exists();
            } else {
                $table = self::getStorageTarget($token);
                return DB::table($table)
                    ->where('token', $token)
                    ->exists();
            }
        } catch (\Exception $e) {
            Log::error('Failed to check token existence: ' . $e->getMessage());
            return true; // Assume it exists to be safe
        }
    }

    public static function getUrlStats(): array
    {
        try {
            $mode = self::getStorageMode();
            $totalUrls = 0;
            $activeUrls = 0;
            $expiredUrls = 0;

            if ($mode === 'multi_db') {
                $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];
                foreach ($connections as $connection) {
                    try {
                        $total = DB::connection($connection)->table('urls')->count();
                        $active = DB::connection($connection)
                            ->table('urls')
                            ->where('expired_at', '>', Carbon::now())
                            ->count();

                        $totalUrls += $total;
                        $activeUrls += $active;
                        $expiredUrls += ($total - $active);
                    } catch (\Exception $e) {
                        // Skip if connection fails
                        continue;
                    }
                }
            } elseif ($mode === 'multi_table') {
                $tables = ['urls_a_f', 'urls_g_l', 'urls_m_r', 'urls_s_z'];
                foreach ($tables as $table) {
                    try {
                        $total = DB::table($table)->count();
                        $active = DB::table($table)
                            ->where('expired_at', '>', Carbon::now())
                            ->count();

                        $totalUrls += $total;
                        $activeUrls += $active;
                        $expiredUrls += ($total - $active);
                    } catch (\Exception $e) {
                        // Skip if table doesn't exist
                        continue;
                    }
                }
            } else {
                $totalUrls = DB::table('urls')->count();
                $activeUrls = DB::table('urls')
                    ->where('expired_at', '>', Carbon::now())
                    ->count();
                $expiredUrls = $totalUrls - $activeUrls;
            }

            return [
                'total_urls' => $totalUrls,
                'active_urls' => $activeUrls,
                'expired_urls' => $expiredUrls,
                'storage_mode' => $mode
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get URL stats: ' . $e->getMessage());
            return [
                'total_urls' => 0,
                'active_urls' => 0,
                'expired_urls' => 0,
                'storage_mode' => $mode ?? 'unknown'
            ];
        }
    }
}
