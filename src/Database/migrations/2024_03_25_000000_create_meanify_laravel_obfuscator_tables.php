<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(config('obfuscator.table', 'obfuscator_failures'), function (Blueprint $table) {
            $table->id();
            $table->string('model')->nullable();
            $table->string('input');
            $table->string('reason');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('obfuscator.table', 'obfuscator_failures'));
    }
};
