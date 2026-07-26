<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_island_gallery_configs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->default('vi');
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('lead')->nullable();
            $table->string('meta_caption')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('locale');
        });

        Schema::create('home_island_gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_config_id')->constrained('home_island_gallery_configs')->cascadeOnDelete();
            $table->string('gcs_path');
            $table->string('alt_text');
            $table->string('title')->nullable();
            $table->string('tag')->nullable();
            $table->string('object_position', 32)->default('center center');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['gallery_config_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_island_gallery_photos');
        Schema::dropIfExists('home_island_gallery_configs');
    }
};

