<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('scope', 40)->default('translation');
            $table->string('seo_type', 80)->nullable();
            $table->string('locale', 20)->nullable();
            $table->text('part_before')->nullable();
            $table->text('part_after')->nullable();
            $table->string('default_model', 160)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['scope', 'is_active']);
            $table->index(['scope', 'seo_type', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompt_templates');
    }
};
