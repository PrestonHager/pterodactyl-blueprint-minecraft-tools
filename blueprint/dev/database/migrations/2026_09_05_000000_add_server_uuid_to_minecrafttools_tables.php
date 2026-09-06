<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('minecrafttools_players') && !Schema::hasColumn('minecrafttools_players', 'server_uuid')) {
            Schema::table('minecrafttools_players', function (Blueprint $table) {
                $table->string('server_uuid', 36)->nullable()->after('id')->index();

                $table->dropUnique(['username']);
                $table->index(['server_uuid', 'username']);
            });
        }

        if (Schema::hasTable('minecrafttools_backups') && !Schema::hasColumn('minecrafttools_backups', 'server_uuid')) {
            Schema::table('minecrafttools_backups', function (Blueprint $table) {
                $table->string('server_uuid', 36)->nullable()->after('id')->index();

                $table->index(['server_uuid', 'file']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('minecrafttools_players', 'server_uuid')) {
            Schema::table('minecrafttools_players', function (Blueprint $table) {
                $table->dropIndex(['server_uuid', 'username']);
                $table->dropColumn('server_uuid');
                $table->unique('username');
            });
        }

        if (Schema::hasColumn('minecrafttools_backups', 'server_uuid')) {
            Schema::table('minecrafttools_backups', function (Blueprint $table) {
                $table->dropIndex(['server_uuid', 'file']);
                $table->dropColumn('server_uuid');
            });
        }
    }
};