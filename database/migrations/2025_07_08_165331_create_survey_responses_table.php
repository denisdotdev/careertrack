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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Nullable for anonymous surveys
            $table->foreignId('question_id')->constrained('survey_questions')->onDelete('cascade');
            $table->text('answer');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            // Ensure one response per user per question (unless multiple responses are allowed)
            $table->unique(['survey_id', 'user_id', 'question_id'], 'unique_user_question_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
