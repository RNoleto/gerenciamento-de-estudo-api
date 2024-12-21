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
        Schema::create('user_study_records', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); 
            $table->integer('ativo')->detault(1);
            $table->unsignedBigInteger('subject_id'); 
            $table->string('topic')->nullable();
            $table->integer('study_time')->default(0);
            $table->integer('total_pauses')->default(0);
            $table->integer('questions_resolved')->nullable();
            $table->integer('correct_answers')->default(0);
            $table->integer('incorrect_answers')->default(0);

            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_study_records');
    }
};
