<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hotel_location_translations')) {
            return;
        }

        if (!Schema::hasColumn('hotel_location_translations', 'display_name')) {
            Schema::table('hotel_location_translations', function (Blueprint $table) {
                $table->text('display_name')->nullable()->after('description');
            });
        }

        DB::statement('
            UPDATE hotel_location_translations t
            INNER JOIN hotel_location h ON h.id = t.hotel_location_id
            SET t.display_name = h.display_name
            WHERE (t.display_name IS NULL OR t.display_name = \'\')
        ');
    }

    public function down(): void
    {
        if (Schema::hasTable('hotel_location_translations') && Schema::hasColumn('hotel_location_translations', 'display_name')) {
            Schema::table('hotel_location_translations', function (Blueprint $table) {
                $table->dropColumn('display_name');
            });
        }
    }
};
