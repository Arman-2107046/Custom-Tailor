<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bodies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('body_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image');

            $table->integer('layer_index')
                ->default(100);

            $table->boolean('is_default')
                ->default(false);

            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            // Prevent duplicate body types for the same fabric
            $table->unique(['fabric_id', 'body_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bodies');
    }
};
