<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chest_pockets', function (Blueprint $table) {
            $table->id();


            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('chest_pocket_type_id')
                ->constrained('chest_pocket_types')
                ->cascadeOnDelete();


            $table->string('image');


            $table->integer('layer_index')->default(100);


            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);


            $table->timestamps();


            $table->unique([
                'fabric_id',
                'chest_pocket_type_id',
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('chest_pockets');
    }
};
