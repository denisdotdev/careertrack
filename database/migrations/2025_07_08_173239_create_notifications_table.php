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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('type'); // survey_available, announcement, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like survey_id, etc.
            $table->enum('status', ['unread', 'read', 'dismissed'])->default('unread');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
