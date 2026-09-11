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
            $table->string('numeroDossier')->unique();
            $table->enum('typeSinistre', [
                'Accident / Collision',
                'Bris de glace',
                'Vol & Vandalisme',
                'Incendie & Panne',
                'Catastrophe naturelle',
                'Autre'
            ]);
            $table->date('dateSinistre');
            $table->time('heureSinistre')->nullable();
            $table->string('lieuSinistre');
            $table->text('description');
            $table->enum('statut', ['En cours', 'Cloture', 'En attente', 'Rejete', 'Rembourse', 'Archive'])->default('En attente');
            $table->softDeletes();

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

