<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\UrlStorageManager;
use Carbon\Carbon;

class Urls extends Model
{
    protected $table = 'urls';
    protected $fillable = ['token', 'original_url', 'expired_at'];

    // Disable timestamps for sharded tables compatibility
    public $timestamps = true;

    protected $dates = ['expired_at', 'created_at', 'updated_at'];

    /**
     * Create a new URL record using the storage manager
     */
    public static function createUrl(string $originalUrl, int $expirationDays = 1): array
    {
        $token = UrlStorageManager::generateUniqueToken();

        $data = [
            'token' => $token,
            'original_url' => $originalUrl,
            'expired_at' => Carbon::now()->addDays($expirationDays),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        $success = UrlStorageManager::storeUrl($data);

        if (!$success) {
            throw new \Exception('Failed to store URL');
        }

        return $data;
    }

    /**
     * Find a URL by token using the storage manager
     */
    public static function findByToken(string $token): ?object
    {
        return UrlStorageManager::findUrl($token);
    }

    /**
     * Update a URL by token
     */
    public static function updateByToken(string $token, array $data): bool
    {
        $data['updated_at'] = Carbon::now();
        return UrlStorageManager::updateUrl($token, $data);
    }

    /**
     * Delete a URL by token
     */
    public static function deleteByToken(string $token): bool
    {
        return UrlStorageManager::deleteUrl($token);
    }

    /**
     * Get URL statistics
     */
    public static function getStats(): array
    {
        return UrlStorageManager::getUrlStats();
    }

    /**
     * Check if a URL is expired
     */
    public static function isExpired(?object $url): bool
    {
        if (!$url || !isset($url->expired_at)) {
            return true;
        }

        return Carbon::parse($url->expired_at)->isPast();
    }

    /**
     * Extend URL expiration
     */
    public static function extendExpiration(string $token, int $days = 1): bool
    {
        $newExpiration = Carbon::now()->addDays($days);
        return self::updateByToken($token, ['expired_at' => $newExpiration]);
    }

    /**
     * Get URL with validation
     */
    public static function getValidUrl(string $token): ?object
    {
        $url = self::findByToken($token);

        if (!$url || self::isExpired($url)) {
            return null;
        }

        return $url;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function generateUrl(): string
    {
        return UrlStorageManager::generateUniqueToken();
    }
}
