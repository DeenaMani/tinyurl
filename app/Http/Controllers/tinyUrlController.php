<?php

namespace App\Http\Controllers;

use App\Models\Urls;
use App\Services\UrlStorageManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class tinyUrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $stats = Urls::getStats();
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get URL stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url|max:2048'
            ]);

            $originalUrl = $request->input('url');
            $expirationDays = $request->input('expiration_days', 1);

            // Validate expiration days
            if ($expirationDays < 1 || $expirationDays > 365) {
                $expirationDays = 1;
            }

            $data = Urls::createUrl($originalUrl, $expirationDays);

            Log::info('URL shortened successfully', [
                'original_url' => $originalUrl,
                'token' => $data['token'],
                'storage_mode' => UrlStorageManager::getStorageMode()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'URL shortened successfully',
                    'data' => [
                        'orginal_url' => $data['original_url'], // Keep typo for frontend compatibility
                        'token' => $data['token'],
                        'shortened_url' => url($data['token']),
                        'expired_at' => $data['expired_at'],
                        'storage_mode' => UrlStorageManager::getStorageMode()
                    ]
                ]);
            }

            // Fallback for non-AJAX requests
            session()->flash('data', [
                'orginal_url' => $data['original_url'],
                'token' => $data['token']
            ]);
            return redirect()->back()->with('data', [
                'orginal_url' => $data['original_url'],
                'token' => $data['token']
            ]);
        } catch (ValidationException $e) {
            Log::warning('URL validation failed', [
                'errors' => $e->errors(),
                'input' => $request->input('url')
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to shorten URL', [
                'error' => $e->getMessage(),
                'url' => $request->input('url'),
                'storage_mode' => UrlStorageManager::getStorageMode()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while shortening the URL'
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while shortening the URL');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $token)
    {
        try {
            $url = Urls::getValidUrl($token);

            if (!$url) {
                Log::info('URL not found or expired', ['token' => $token]);
                return redirect()->to('/')->with('error', 'URL not found or expired');
            }

            Log::info('URL redirected successfully', [
                'token' => $token,
                'original_url' => $url->original_url
            ]);

            return redirect()->to($url->original_url);
        } catch (\Exception $e) {
            Log::error('Failed to redirect URL', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return redirect()->to('/')->with('error', 'An error occurred while processing the URL');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $token)
    {
        try {
            $url = Urls::findByToken($token);

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $url
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve URL for editing', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve URL'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $token)
    {
        try {
            $request->validate([
                'original_url' => 'sometimes|url|max:2048',
                'expired_at' => 'sometimes|date|after:now'
            ]);

            $updateData = [];

            if ($request->has('original_url')) {
                $updateData['original_url'] = $request->input('original_url');
            }

            if ($request->has('expired_at')) {
                $updateData['expired_at'] = Carbon::parse($request->input('expired_at'));
            }

            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data provided for update'
                ], 400);
            }

            $success = Urls::updateByToken($token, $updateData);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update URL'
                ], 500);
            }

            Log::info('URL updated successfully', [
                'token' => $token,
                'updates' => $updateData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'URL updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update URL', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the URL'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $token)
    {
        try {
            $success = Urls::deleteByToken($token);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL not found or already deleted'
                ], 404);
            }

            Log::info('URL deleted successfully', ['token' => $token]);

            return response()->json([
                'success' => true,
                'message' => 'URL deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete URL', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the URL'
            ], 500);
        }
    }

    /**
     * Extend URL expiration
     */
    public function extend(Request $request, string $token)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:1|max:365'
            ]);

            $days = $request->input('days');
            $success = Urls::extendExpiration($token, $days);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to extend URL expiration'
                ], 500);
            }

            Log::info('URL expiration extended', [
                'token' => $token,
                'days' => $days
            ]);

            return response()->json([
                'success' => true,
                'message' => "URL expiration extended by {$days} days"
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to extend URL expiration', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while extending URL expiration'
            ], 500);
        }
    }

    /**
     * Get storage information
     */
    public function storageInfo()
    {
        try {
            $storageMode = UrlStorageManager::getStorageMode();
            $stats = Urls::getStats();

            return response()->json([
                'success' => true,
                'data' => [
                    'storage_mode' => $storageMode,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get storage info', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve storage information'
            ], 500);
        }
    }
}
