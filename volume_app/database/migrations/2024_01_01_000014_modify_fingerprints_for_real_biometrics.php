<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fingerprints', function (Blueprint $table) {
            $table->dropIndex('fingerprints_template_code_index');
        });

        Schema::table('fingerprints', function (Blueprint $table) {
            $table->string('template_code', 1024)->change();
        });
    }

    public function down(): void
    {
        Schema::table('fingerprints', function (Blueprint $table) {
            $table->string('template_code', 500)->change();
        });

        Schema::table('fingerprints', function (Blueprint $table) {
            $table->index('template_code', 'fingerprints_template_code_index');
        });
    }
};
