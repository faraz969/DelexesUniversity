<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subject_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_record_id')->index();
            $table->string('subject', 150)->nullable();
            $table->string('grade_letter', 10)->nullable();
            $table->unsignedTinyInteger('grade_number')->nullable();
            $table->boolean('is_best_six')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subject_grades');
    }
};

