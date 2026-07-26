<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_reviews_configs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->default('vi');
            $table->string('kicker')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('score_value', 2, 1)->default(4.9);
            $table->json('score_stats')->nullable();
            $table->string('partners_label')->nullable();
            $table->json('partners')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('locale');
        });

        Schema::create('home_review_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviews_config_id')->constrained('home_reviews_configs')->cascadeOnDelete();
            $table->text('quote_text');
            $table->string('customer_name');
            $table->string('customer_meta')->nullable();
            $table->string('tag')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['reviews_config_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_review_items');
        Schema::dropIfExists('home_reviews_configs');
    }
};
