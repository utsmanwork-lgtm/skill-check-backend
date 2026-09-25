<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained('skills')->onDelete('cascade');
            $table->string('code');
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['skill_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
