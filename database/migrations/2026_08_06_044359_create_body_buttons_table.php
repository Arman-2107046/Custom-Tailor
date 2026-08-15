<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('body_buttons', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Body Type
            |--------------------------------------------------------------------------
            |
            | Example:
            | SB1, SB2, DB2, DB4, etc.
            |
            */
            $table->foreignId('body_type_id')
                ->constrained('body_types')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Button Image
            |--------------------------------------------------------------------------
            */
            $table->foreignId('button_image_id')
                ->constrained('button_images')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Button Rendering Image
            |--------------------------------------------------------------------------
            */
            $table->string('image');

            /*
            |--------------------------------------------------------------------------
            | Frontend Rendering Order
            |--------------------------------------------------------------------------
            */
            $table->integer('layer_index')
                ->default(160);

            /*
            |--------------------------------------------------------------------------
            | Default Button
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_default')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Active Status
            |--------------------------------------------------------------------------
            */
            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Button Type Per Body Type
            |--------------------------------------------------------------------------
            |
            | A body type can have each button image only once.
            |
            */
            $table->unique([
                'body_type_id',
                'button_image_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('body_buttons');
    }
};
