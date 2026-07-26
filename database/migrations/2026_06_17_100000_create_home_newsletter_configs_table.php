<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_newsletter_configs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->default('vi');
            $table->string('stamp_text', 16)->nullable();
            $table->string('stamp_year', 8)->nullable();
            $table->string('kicker')->nullable();
            $table->string('title')->nullable();
            $table->text('lead')->nullable();
            $table->string('field_label')->nullable();
            $table->string('email_placeholder')->nullable();
            $table->string('submit_text')->nullable();
            $table->text('note')->nullable();
            $table->string('sign_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_newsletter_configs');
    }
};
