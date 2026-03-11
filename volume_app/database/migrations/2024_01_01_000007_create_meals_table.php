<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('operator_id')->constrained('users');
            $table->enum('method', ['biometric', 'manual']);
            $table->text('manual_reason')->nullable();
            $table->dateTime('served_at');
            $table->boolean('synced')->default(true);
            $table->timestamps();

            $table->index(['student_id', 'served_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
