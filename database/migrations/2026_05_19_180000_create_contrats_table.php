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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->string('numeroContrat');
            $table->string('typeContrat');
            $table->date('dateDebut');
            $table->date('dateFin');
            $table->decimal('franchise', 10, 2);
            $table->decimal('prime', 10, 2);
            $table->string('garantie');
            
            // CHAMPS SPÉCIFIQUES MVP AUTOMOBILE
            $table->foreignId('vehicule_id')->constrained('vehicules')->onDelete('cascade');
            

            $table->foreignId('assure_id')->constrained('users');
           // $table->foreignId('sinistre_id')->nullable()->constrained('sinistres');
            $table->softDeletes(); // Pour archivage logique (remplace la suppression physique)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
