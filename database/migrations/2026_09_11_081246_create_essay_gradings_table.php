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
        Schema::create('essay_gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_answer_id')->constrained()->cascadeOnDelete();
            $table->decimal('ai_suggested_score', 8, 2)->nullable();
            $table->decimal('teacher_score', 8, 2)->nullable();
            $table->longText('ai_feedback')->nullable();
            $table->longText('teacher_feedback')->nullable();
            $table->json('grading_details')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('essay_gradings');
    }
};
