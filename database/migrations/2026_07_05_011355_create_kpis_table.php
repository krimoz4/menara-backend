<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('kpis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('kpi_category_id')->constrained()->onDelete('cascade');
        $table->string('name', 150);
        $table->text('description')->nullable();
        $table->string('unit', 50);
        $table->decimal('target_value', 10, 2);
        $table->boolean('is_higher_better')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
