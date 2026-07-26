<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_hero_configs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->default('vi');
            $table->string('title');
            $table->string('title_accent')->nullable();
            $table->text('tagline')->nullable();
            $table->string('btn_primary_label')->nullable();
            $table->string('btn_primary_url')->nullable();
            $table->boolean('btn_primary_enabled')->default(true);
            $table->string('btn_secondary_label')->nullable();
            $table->string('btn_secondary_url')->nullable();
            $table->boolean('btn_secondary_enabled')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('locale');
        });

        Schema::create('home_hero_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_config_id')->constrained('home_hero_configs')->cascadeOnDelete();
            $table->string('gcs_path');
            $table->string('public_url');
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('home_hero_route_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_config_id')->constrained('home_hero_configs')->cascadeOnDelete();
            $table->unsignedBigInteger('ship_location_id');
            $table->unsignedBigInteger('ship_id')->nullable();
            $table->string('label_from')->nullable();
            $table->string('label_to')->nullable();
            $table->string('duration_label')->nullable();
            $table->string('price_label')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hero_config_id', 'sort_order']);
            $table->index('ship_location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_route_slots');
        Schema::dropIfExists('home_hero_backgrounds');
        Schema::dropIfExists('home_hero_configs');
    }
};
