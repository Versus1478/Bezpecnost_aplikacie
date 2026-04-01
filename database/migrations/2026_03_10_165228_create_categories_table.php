<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();
            $table->string('color', 16)->default('#808080');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
