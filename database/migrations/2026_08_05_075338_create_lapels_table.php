<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lapels', function (Blueprint $table) {

            $table->id();


            // Fabric relationship
            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();


            // Body relationship
            $table->foreignId('body_id')
                ->constrained()
                ->cascadeOnDelete();


            // Lapel category relationship
            $table->foreignId('lapel_category_id')
                ->constrained()
                ->cascadeOnDelete();


            // Lapel subcategory relationship
            // Custom table name because migration uses lapel_sub_categories
            $table->foreignId('lapel_subcategory_id')
                ->constrained('lapel_sub_categories')
                ->cascadeOnDelete();



            // Specific lapel image for this combination
            $table->string('image');


            // Frontend rendering layer order
            $table->integer('layer_index')
                ->default(100);


            // One default lapel per body/fabric
            $table->boolean('is_default')
                ->default(false);


            $table->boolean('status')
                ->default(true);


            $table->timestamps();



            // Prevent duplicate combinations
            $table->unique(
                [
                    'fabric_id',
                    'body_id',
                    'lapel_category_id',
                    'lapel_subcategory_id'
                ],
                'lapel_combination_unique'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('lapels');
    }
};
