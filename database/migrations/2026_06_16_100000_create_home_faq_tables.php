<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_faq_configs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->default('vi');
            $table->string('kicker')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('help_title')->nullable();
            $table->text('help_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('locale');
        });

        Schema::create('home_faq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_config_id')->constrained('home_faq_configs')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer_html');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_open_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['faq_config_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_faq_items');
        Schema::dropIfExists('home_faq_configs');
    }
};
