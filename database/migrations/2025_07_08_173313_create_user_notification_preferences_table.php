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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // survey_available, announcement, etc.
            $table->boolean('email_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();

            // Ensure one preference per user per company per notification type
            $table->unique(['user_id', 'company_id', 'notification_type']);
            
            // Indexes for performance
            $table->index(['user_id', 'company_id']);
            $table->index('notification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
