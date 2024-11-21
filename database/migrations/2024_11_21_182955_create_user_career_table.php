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
        Schema::create('user_career', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedBigInteger('career_id');
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // Relacionamento com a tabela de carreiras
            $table->foreign('career_id')->references('id')->on('careers')->onDelete('cascade');

            //Cada usuário pode ter apenas uma carreira
            $table->unique('user_id', 'unique_user_career');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_career');
    }
};
