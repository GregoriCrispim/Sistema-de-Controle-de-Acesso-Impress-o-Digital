<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_id')->constrained('users');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_meals');
            $table->decimal('meal_value', 10, 2);
            $table->decimal('total_value', 10, 2);
            $table->integer('biometric_count');
            $table->integer('manual_count');
            $table->string('protocol_number')->unique();
            $table->dateTime('validated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_validations');
    }
};
