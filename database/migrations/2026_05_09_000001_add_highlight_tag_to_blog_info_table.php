<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_info', function (Blueprint $table) {
            $table->string('highlight_tag', 32)->nullable()->after('outstanding');
        });
    }

    public function down(): void
    {
        Schema::table('blog_info', function (Blueprint $table) {
            $table->dropColumn('highlight_tag');
        });
    }
};
