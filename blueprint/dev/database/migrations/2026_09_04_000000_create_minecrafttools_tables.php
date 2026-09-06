<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minecrafttools_players', function (Blueprint $table) {
            $table->id();
            $table->string('server_uuid', 36)->nullable()->index();
            $table->string('username');
            $table->string('uuid', 36)->nullable();
            $table->string('display_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['server_uuid', 'username']);
        });

        Schema::create('minecrafttools_backups', function (Blueprint $table) {
            $table->id();
            $table->string('server_uuid', 36)->nullable()->index();
            $table->string('file');
            $table->mediumText('content')->nullable();
            $table->timestamps();

            $table->index(['server_uuid', 'file']);
        });

        Schema::create('minecrafttools_keyvalues', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minecrafttools_players');
        Schema::dropIfExists('minecrafttools_backups');
        Schema::dropIfExists('minecrafttools_keyvalues');
    }
};