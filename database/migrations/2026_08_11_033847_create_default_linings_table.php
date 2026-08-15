<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_linings', function (Blueprint $table) {

            $table->id();

            // Fabric relationship
            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();

            // Body Type relationship
            $table->foreignId('body_type_id')
                ->constrained('body_types')
                ->cascadeOnDelete();

            // Lining type relationship
            $table->foreignId('lining_type_id')
                ->constrained('lining_types')
                ->cascadeOnDelete();

            // Specific default lining image
            $table->string('image')
                ->nullable();

            // Frontend rendering layer order
            $table->integer('layer_index')
                ->default(0);

            // Active/inactive
            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            // One default lining per fabric/body type combination
            $table->unique(
                [
                    'fabric_id',
                    'body_type_id',
                ],
                'default_lining_combination_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_linings');
    }
};
