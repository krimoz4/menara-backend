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
    Schema::create('kpi_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('kpi_id')->constrained()->onDelete('cascade');
        $table->foreignId('department_id')->constrained()->onDelete('cascade');
        $table->decimal('recorded_value', 10, 2);
        $table->date('recorded_date');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_records');
    }
};
