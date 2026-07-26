<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung cột hiển thị vào bảng dịch entity:
 * - air_location_translations.display_name
 * - guide_info_translations.display_name
 * - carrental_location_translations.location_name (Khu vực hiển thị)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('air_location_translations')
            && !Schema::hasColumn('air_location_translations', 'display_name')) {
            Schema::table('air_location_translations', function (Blueprint $table) {
                $table->text('display_name')->nullable()->after('description');
            });
            DB::statement('
                UPDATE air_location_translations t
                INNER JOIN air_location a ON a.id = t.air_location_id
                SET t.display_name = a.display_name
                WHERE (t.display_name IS NULL OR t.display_name = \'\')
            ');
        }

        if (Schema::hasTable('guide_info_translations')
            && !Schema::hasColumn('guide_info_translations', 'display_name')) {
            Schema::table('guide_info_translations', function (Blueprint $table) {
                $table->text('display_name')->nullable()->after('description');
            });
            DB::statement('
                UPDATE guide_info_translations t
                INNER JOIN guide_info g ON g.id = t.guide_info_id
                SET t.display_name = g.display_name
                WHERE (t.display_name IS NULL OR t.display_name = \'\')
            ');
        }

        if (Schema::hasTable('carrental_location_translations')
            && !Schema::hasColumn('carrental_location_translations', 'location_name')) {
            Schema::table('carrental_location_translations', function (Blueprint $table) {
                $table->text('location_name')->nullable()->after('description');
            });
            DB::statement('
                UPDATE carrental_location_translations t
                INNER JOIN carrental_location c ON c.id = t.carrental_location_id
                SET t.location_name = c.location_name
                WHERE (t.location_name IS NULL OR t.location_name = \'\')
            ');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('air_location_translations') && Schema::hasColumn('air_location_translations', 'display_name')) {
            Schema::table('air_location_translations', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
        if (Schema::hasTable('guide_info_translations') && Schema::hasColumn('guide_info_translations', 'display_name')) {
            Schema::table('guide_info_translations', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
        if (Schema::hasTable('carrental_location_translations') && Schema::hasColumn('carrental_location_translations', 'location_name')) {
            Schema::table('carrental_location_translations', function (Blueprint $table) {
                $table->dropColumn('location_name');
            });
        }
    }
};
