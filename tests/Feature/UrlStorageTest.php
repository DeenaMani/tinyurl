<?php

use App\Services\UrlStorageManager;
use App\Models\Urls;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('url storage manager generates unique tokens', function () {
    $token1 = UrlStorageManager::generateUniqueToken();
    $token2 = UrlStorageManager::generateUniqueToken();

    expect($token1)->not->toBe($token2);
    expect(strlen($token1))->toBe(8);
    expect(strlen($token2))->toBe(8);
});

test('url storage manager determines correct storage target', function () {
    config(['app.storage_mode' => 'multi_table']);

    $target1 = UrlStorageManager::getStorageTarget('A1234567');
    $target2 = UrlStorageManager::getStorageTarget('H1234567');
    $target3 = UrlStorageManager::getStorageTarget('N1234567');
    $target4 = UrlStorageManager::getStorageTarget('T1234567');

    expect($target1)->toBe('urls_a_f');
    expect($target2)->toBe('urls_g_l');
    expect($target3)->toBe('urls_m_r');
    expect($target4)->toBe('urls_s_z');
});

test('urls model creates url successfully', function () {
    $originalUrl = 'https://example.com/very-long-url';
    $data = Urls::createUrl($originalUrl);

    expect($data)->toHaveKeys(['token', 'original_url', 'expired_at', 'created_at', 'updated_at']);
    expect($data['original_url'])->toBe($originalUrl);
    expect($data['token'])->toBeString();
    expect(strlen($data['token']))->toBe(8);
});

test('urls model finds url by token', function () {
    $originalUrl = 'https://example.com/test-url';
    $data = Urls::createUrl($originalUrl);

    $found = Urls::findByToken($data['token']);

    expect($found)->not->toBeNull();
    expect($found->original_url)->toBe($originalUrl);
    expect($found->token)->toBe($data['token']);
});

test('urls model returns null for non-existent token', function () {
    $found = Urls::findByToken('NONEXIST');

    expect($found)->toBeNull();
});

test('url shortening endpoint works via ajax', function () {
    $response = $this->postJson('/tiny-url', [
        'url' => 'https://example.com/test-ajax'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'URL shortened successfully'
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'orginal_url',
                'token',
                'shortened_url',
                'expired_at',
                'storage_mode'
            ]
        ]);
});

test('url shortening validates input', function () {
    $response = $this->postJson('/tiny-url', [
        'url' => 'not-a-valid-url'
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed'
        ]);
});

test('url redirect works correctly', function () {
    $originalUrl = 'https://example.com/redirect-test';
    $data = Urls::createUrl($originalUrl);

    $response = $this->get('/' . $data['token']);

    $response->assertRedirect($originalUrl);
});

test('expired url redirect fails', function () {
    $originalUrl = 'https://example.com/expired-test';
    $data = Urls::createUrl($originalUrl, -1); // Create expired URL

    $response = $this->get('/' . $data['token']);

    $response->assertRedirect('/');
});

test('storage stats endpoint works', function () {
    $response = $this->getJson('/api/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_urls',
                'active_urls',
                'expired_urls',
                'storage_mode'
            ]
        ]);
});

test('storage info endpoint works', function () {
    $response = $this->getJson('/api/storage-info');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'storage_mode',
                'stats'
            ]
        ]);
});
