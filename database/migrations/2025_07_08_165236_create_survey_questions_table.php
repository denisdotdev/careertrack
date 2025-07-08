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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('question_text');
            $table->enum('question_type', [
                'text',
                'textarea',
                'multiple_choice',
                'checkbox',
                'rating',
                'date',
                'email'
            ]);
            $table->json('options')->nullable(); // For multiple choice, checkbox questions
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
