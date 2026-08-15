<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_lining_fabrics', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('image');

            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            $table->unique('name');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_lining_fabrics');
    }
};
