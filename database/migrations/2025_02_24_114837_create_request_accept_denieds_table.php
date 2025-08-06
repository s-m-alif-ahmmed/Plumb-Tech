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
        Schema::create('request_accept_denieds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('discussion_requests')->onDelete('cascade');
            $table->foreignId('engineer_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending','accepted', 'denied'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_accept_denieds');
    }
};