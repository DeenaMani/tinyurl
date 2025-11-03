<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('tinyurl.storage_mode') !== 'single') {
            return;
        }

        Schema::create('urls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 20)->unique()->index();
            $table->longText('original_url');
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};