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
        Schema::create('daily_progress', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->foreignId('schedule_item_id')->constrained('schedule_items')->onDelete('cascade');
            $table->date('completion_date');
            $table->timestamps();

            $table->unique(['user_id', 'schedule_item_id', 'completion_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_progress');
    }
};
