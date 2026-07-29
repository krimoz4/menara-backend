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
        Schema::create('glpi_projects', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('responsable');
            $table->string('statut'); // En cours, Terminé, En attente
            $table->integer('progression'); // de 0 à 100
            $table->string('date_echeance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glpi_projects');
    }
};
