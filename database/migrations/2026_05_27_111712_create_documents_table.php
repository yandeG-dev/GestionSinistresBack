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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('chemin_fichier'); // ex: lien vers le s3 ou chemin local
            $table->string('type_document')->nullable(); // ex: pdf, jpeg
            // Relation 1..N avec Sinistre
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
        Schema::dropIfExists('documents');
    }
};
