<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rx_extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('extension_id', 64)->unique();
            $table->string('name');
            $table->string('version');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->text('icon')->nullable();
            $table->string('website')->nullable();
            $table->boolean('installed')->default(false);
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('rx_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rx_extensions');
        Schema::dropIfExists('rx_settings');
    }
};
