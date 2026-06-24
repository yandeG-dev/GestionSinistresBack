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
        Schema::create('quittances', function (Blueprint $table) {
            $table->id();
            $table->string('numeroQuittance')->unique();
            $table->decimal('montantVerse', 10, 2);
            $table->enum('modeReglement', ['Virement Bancaire', 'Chèque', 'Espèces', 'Mobile Money']);
            $table->string('beneficiaire');
            $table->foreignId('sinistre_id')->constrained('sinistres')->onDelete('cascade'); // Lien 1-1 avec Sinistre (UML)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quittances');
    }
};
