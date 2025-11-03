<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $suffixes = ['a_f', 'g_l', 'm_r', 's_z'];

    public function up(): void
    {
        if (config('tinyurl.storage_mode') !== 'multi_table') {
            return;
        }

        foreach ($this->suffixes as $suffix) {
            Schema::create("urls_{$suffix}", function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('token', 20)->unique()->index();
                $table->longText('original_url');
                $table->dateTime('expired_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->suffixes as $suffix) {
            Schema::dropIfExists("urls_{$suffix}");
        }
    }
};