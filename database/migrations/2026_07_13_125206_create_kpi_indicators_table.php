<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('kpi_indicators', function (Blueprint $table) {
        $table->id();
        $table->string('objectif');
        $table->string('indicateur');
        $table->string('type_indicateur')->default('Performance');
        $table->string('cible_texte');
        $table->float('cible_valeur');
        $table->float('m1')->nullable();
        $table->float('m2')->nullable();
        $table->float('m3')->nullable();
        $table->float('m4')->nullable();
        $table->float('m5')->nullable();
        $table->float('m6')->nullable();
        $table->float('m7')->nullable();
        $table->float('m8')->nullable();
        $table->float('m9')->nullable();
        $table->float('m10')->nullable();
        $table->float('m11')->nullable();
        $table->float('m12')->nullable();
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
    }
};
