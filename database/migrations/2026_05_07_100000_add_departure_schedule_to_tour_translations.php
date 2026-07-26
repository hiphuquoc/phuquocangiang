<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['tour_info_translations', 'tour_info_foreign_translations'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'departure_schedule')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->text('departure_schedule')->nullable()->after('transport');
                });
            }
        }

        $this->backfillFromMaster('tour_info', 'tour_info_translations', 'tour_info_id');
        $this->backfillFromMaster('tour_info_foreign', 'tour_info_foreign_translations', 'tour_info_foreign_id');
    }

    private function backfillFromMaster(string $masterTable, string $transTable, string $fk): void
    {
        if (!Schema::hasTable($masterTable) || !Schema::hasTable($transTable)) {
            return;
        }
        if (!Schema::hasColumn($masterTable, 'departure_schedule') || !Schema::hasColumn($transTable, 'departure_schedule')) {
            return;
        }

        $defaultLangId = (int) (DB::table('languages')->where('code', config('language.default_code', 'vi'))->value('id') ?? 0);
        if ($defaultLangId <= 0) {
            return;
        }

        $rows = DB::table($transTable)->where('language_id', $defaultLangId)->get();
        foreach ($rows as $row) {
            $fkVal = $row->{$fk} ?? null;
            if (!$fkVal) {
                continue;
            }
            $src = DB::table($masterTable)->where('id', $fkVal)->value('departure_schedule');
            if ($src === null || $src === '') {
                continue;
            }
            DB::table($transTable)->where('id', $row->id)->update([
                'departure_schedule' => $src,
                'updated_at'         => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach (['tour_info_translations', 'tour_info_foreign_translations'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'departure_schedule')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('departure_schedule');
                });
            }
        }
    }
};
