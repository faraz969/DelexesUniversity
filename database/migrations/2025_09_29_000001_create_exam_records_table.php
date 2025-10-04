<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->index();
            $table->string('exam_type', 100)->nullable();
            $table->string('sitting_exam', 150)->nullable();
            $table->string('year', 10)->nullable();
            $table->string('index_number', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_records');
    }
};

