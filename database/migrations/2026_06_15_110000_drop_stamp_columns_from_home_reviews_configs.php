<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_reviews_configs')) {
            return;
        }

        Schema::table('home_reviews_configs', function (Blueprint $table) {
            if (Schema::hasColumn('home_reviews_configs', 'stamp_line1')) {
                $table->dropColumn('stamp_line1');
            }
            if (Schema::hasColumn('home_reviews_configs', 'stamp_line2')) {
                $table->dropColumn('stamp_line2');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('home_reviews_configs')) {
            return;
        }

        Schema::table('home_reviews_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('home_reviews_configs', 'stamp_line1')) {
                $table->string('stamp_line1', 32)->nullable()->after('partners');
            }
            if (!Schema::hasColumn('home_reviews_configs', 'stamp_line2')) {
                $table->string('stamp_line2', 32)->nullable()->after('stamp_line1');
            }
        });
    }
};
