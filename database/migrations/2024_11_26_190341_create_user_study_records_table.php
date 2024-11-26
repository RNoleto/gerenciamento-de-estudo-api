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
            $table->string('user_id'); // ID do usuário
            $table->unsignedBigInteger('subject_id'); // ID da matéria
            $table->string('topic')->nullable(); // Tópico, não obrigatório
            $table->integer('study_time')->default(0); // Tempo de estudo em minutos
            $table->integer('total_pauses')->default(0); // Total de pausas
            $table->integer('questions_resolved')->nullable(); // Questões respondidas
            $table->integer('correct_answers')->default(0); // Questões corretas
            $table->integer('incorrect_answers')->default(0); // Questões incorretas

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
