<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ship_location_translations')) {
            return;
        }

        if (!Schema::hasColumn('ship_location_translations', 'display_name')) {
            Schema::table('ship_location_translations', function (Blueprint $table) {
                $table->string('display_name')->nullable()->after('name');
            });
        }

        // Backfill display_name từ bảng gốc cho các row translation đang có.
        DB::statement("
            UPDATE ship_location_translations t
            INNER JOIN ship_location s ON s.id = t.ship_location_id
            SET t.display_name = s.display_name
            WHERE t.display_name IS NULL OR t.display_name = ''
        ");
    }

    public function down(): void
    {
        if (Schema::hasTable('ship_location_translations') && Schema::hasColumn('ship_location_translations', 'display_name')) {
            Schema::table('ship_location_translations', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
    }
};
