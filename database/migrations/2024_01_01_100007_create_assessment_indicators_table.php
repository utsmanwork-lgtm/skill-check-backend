<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->boolean('checked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['assessment_id', 'indicator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_indicators');
    }
};
