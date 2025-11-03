<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $connections = ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4'];

    public function up(): void
    {
        if (config('tinyurl.storage_mode') !== 'multi_db') {
            return;
        }

        foreach ($this->connections as $connection) {
            Schema::connection($connection)->create('urls', function (Blueprint $table) {
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
        foreach ($this->connections as $connection) {
            Schema::connection($connection)->dropIfExists('urls');
        }
    }
};