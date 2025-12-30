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
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->string('provider');
            $table->string('external_id');
            $table->string('title');
            $table->string('category'); // slots | live | table
            $table->boolean('is_active')->default(false);
            $table->decimal('rtp', 5, 2)->nullable();

            $table->timestamps();
            $table->unique(['provider', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
