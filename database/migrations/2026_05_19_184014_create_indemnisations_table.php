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
            $table->id();
            $table->decimal('montantBrute', 10, 2);
            $table->decimal('montantFranchise', 10, 2)->nullable();
            $table->decimal('montantNet', 10, 2)->nullable();
            $table->enum('statutIndemnisation', ['En attente', 'Validé', 'Refusé', 'Payé'])->default('En attente');
            $table->date('dateApprobation')->nullable();
            $table->foreignId('sinistre_id')->constrained('sinistres')->onDelete('cascade');
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
