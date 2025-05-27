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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->integer('duration_minutes')->default(60); // Service duration
            $table->boolean('is_active')->default(true);
            $table->json('images')->nullable(); // Store multiple image URLs
            $table->timestamps();

            $table->index(['service_category_id', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
