<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'numeroContrat',
        'typeContrat',
        'dateDebut',
        'dateFin',
        'franchise',
        'prime',
        'garantie',
        'nomSouscripteur',
        'policeAssurance',
        'immatriculation',
        'marque_vehicule',
        'modele_vehicule',
        'assure_id',
    ];

    protected $casts = [
        'dateDebut' => 'date',
        'dateFin' => 'date',
        'franchise' => 'decimal:2',
        'prime' => 'decimal:2',
    ];
}
