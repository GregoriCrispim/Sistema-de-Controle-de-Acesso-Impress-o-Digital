<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('template_code', 500);
            $table->tinyInteger('finger_index');
            $table->timestamps();

            $table->index('template_code', 'fingerprints_template_code_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprints');
    }
};
