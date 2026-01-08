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
        // Programming Languages
        Schema::create('programming_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Frameworks
        Schema::create('frameworks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('programming_language_id')->nullable()->constrained('programming_languages')->nullOnDelete();
            $table->timestamps();
            $table->index(['programming_language_id', 'name']);
        });

        // Databases
        Schema::create('databases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Server Locations
        Schema::create('server_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frameworks');
        Schema::dropIfExists('programming_languages');
        Schema::dropIfExists('databases');
        Schema::dropIfExists('server_locations');
    }
};
