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
        Schema::create('sinistres', function (Blueprint $table) {
            $table->id(); 
            $table->date('dateSinistre');
            $table->string('description');
            $table->string('lieuSinistre');
            $table->enum('statut', ['En cours', 'Clôturé', 'En attente', 'Rejeté', 'Remboursé', 'Archivé'])->default('En attente');
            $table->softDeletes(); // Pour archivage logique
            
            $table->foreignId('assure_id')->constrained('users');
            $table->foreignId('contrat_id')->nullable()->constrained('contrats');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinistres');
    }
};
