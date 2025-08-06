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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade'); // যে রেটিং দিচ্ছে
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // যে রেটিং পাচ্ছে
            $table->tinyInteger('rating')->unsigned()->comment('1 to 5 stars'); // Rating (1-5)
            $table->text('review')->nullable(); // Optional Review
            $table->timestamps();

            // Ensure one user can rate another user only once
            $table->unique(['reviewer_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
