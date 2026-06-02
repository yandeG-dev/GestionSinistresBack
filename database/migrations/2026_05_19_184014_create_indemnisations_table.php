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
        Schema::create('indemnisations', function (Blueprint $table) {
            $table->id('idIndemnisation');
            $table->decimal('montantBrute', 10, 2);
            $table->enum('statutPaiement', ['Payé', 'En attente', 'Rejeté'])->default('En attente');
            $table->unsignedBigInteger('sinistre_id');
            $table->foreign('sinistre_id')->references('idSinistre')->on('sinistres')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indemnisations');
    }
};
