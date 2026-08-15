<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapel_sub_categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Subcategory diagram
            $table->string('diagram')->nullable();

            // Active/inactive
            $table->boolean('status')
                ->default(true);

            // Default subcategory
            $table->boolean('is_default')
                ->default(false);

            $table->timestamps();

            // Slim, Standard, Wide, etc. should only exist once
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lapel_sub_categories');
    }
};
