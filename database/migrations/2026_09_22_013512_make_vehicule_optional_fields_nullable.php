<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->string('type_carburant')->nullable()->change();
            $table->integer('puissance_fiscale')->nullable()->change();
            $table->date('date_mise_en_circulation')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicules', function (Blueprint $table) {
            $table->string('type_carburant')->nullable(false)->change();
            $table->integer('puissance_fiscale')->nullable(false)->change();
            $table->date('date_mise_en_circulation')->nullable(false)->change();
        });
    }
};
