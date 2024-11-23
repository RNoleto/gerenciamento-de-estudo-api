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
        Schema::create('user_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subjects');
    }
};
