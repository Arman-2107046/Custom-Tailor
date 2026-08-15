<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sleeves', function (Blueprint $table) {
            $table->id();


            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('sleeve_type_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->string('image');


            // Rendering order
            $table->integer('layer_index')
                ->default(100);


            $table->boolean('is_default')
                ->default(false);


            $table->boolean('status')
                ->default(true);


            $table->timestamps();


            // One sleeve type per fabric only once
            $table->unique([
                'fabric_id',
                'sleeve_type_id'
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sleeves');
    }
};
