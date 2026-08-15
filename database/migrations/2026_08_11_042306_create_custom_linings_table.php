<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_linings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Original Fabric
            |--------------------------------------------------------------------------
            */

            $table->foreignId('fabric_id')
                ->constrained()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Custom Lining Fabric
            |--------------------------------------------------------------------------
            */

            $table->foreignId('custom_lining_fabric_id')
                ->constrained('custom_lining_fabrics')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Lining Type
            |--------------------------------------------------------------------------
            */

            $table->foreignId('lining_type_id')
                ->constrained('lining_types')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Lining Image
            |--------------------------------------------------------------------------
            */

            $table->string('image');


            /*
            |--------------------------------------------------------------------------
            | Frontend Rendering Order
            |--------------------------------------------------------------------------
            */

            $table->integer('layer_index')
                ->default(100);


            /*
            |--------------------------------------------------------------------------
            | Default Custom Lining
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
            | Prevent Duplicate Custom Lining
            |--------------------------------------------------------------------------
            |
            | One combination of:
            |
            | Original Fabric
            | + Custom Lining Fabric
            | + Lining Type
            |
            | should represent one Custom Lining.
            |
            */

            $table->unique(
                [
                    'fabric_id',
                    'custom_lining_fabric_id',
                    'lining_type_id',
                ],
                'custom_lining_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_linings');
    }
};
