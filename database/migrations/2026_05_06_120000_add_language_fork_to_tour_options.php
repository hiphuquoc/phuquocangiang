<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tour_option')) {
            Schema::table('tour_option', function (Blueprint $table) {
                if (!Schema::hasColumn('tour_option', 'language_id')) {
                    $table->unsignedBigInteger('language_id')->nullable()->after('name');
                }
                if (!Schema::hasColumn('tour_option', 'fork_source_id')) {
                    $table->unsignedBigInteger('fork_source_id')->nullable()->after('language_id');
                }
            });
        }
        if (Schema::hasTable('tour_option_foreign')) {
            Schema::table('tour_option_foreign', function (Blueprint $table) {
                if (!Schema::hasColumn('tour_option_foreign', 'language_id')) {
                    $table->unsignedBigInteger('language_id')->nullable()->after('option');
                }
                if (!Schema::hasColumn('tour_option_foreign', 'fork_source_id')) {
                    $table->unsignedBigInteger('fork_source_id')->nullable()->after('language_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tour_option')) {
            Schema::table('tour_option', function (Blueprint $table) {
                if (Schema::hasColumn('tour_option', 'fork_source_id')) {
                    $table->dropColumn('fork_source_id');
                }
                if (Schema::hasColumn('tour_option', 'language_id')) {
                    $table->dropColumn('language_id');
                }
            });
        }
        if (Schema::hasTable('tour_option_foreign')) {
            Schema::table('tour_option_foreign', function (Blueprint $table) {
                if (Schema::hasColumn('tour_option_foreign', 'fork_source_id')) {
                    $table->dropColumn('fork_source_id');
                }
                if (Schema::hasColumn('tour_option_foreign', 'language_id')) {
                    $table->dropColumn('language_id');
                }
            });
        }
    }
};
