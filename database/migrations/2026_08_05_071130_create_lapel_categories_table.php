<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapel_categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Category diagram image
            $table->string('diagram')->nullable();

            // Active/inactive
            $table->boolean('status')
                ->default(true);

            // Default category
            $table->boolean('is_default')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lapel_categories');
    }
};
