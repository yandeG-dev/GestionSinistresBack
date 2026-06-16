<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sinistre extends Model
{
    use HasFactory;

    protected $fillable = [
        'dateSinistre',
        'description',
        'lieuSinistre',
        'statut',
        'donnees_specifiques',
        'type_id',
        'assure_id',
        'contrat_id'
    ];

    // C'est ICI qu'on dit à Laravel que la case magique est un tableau (Array)
    protected $casts = [
        'donnees_specifiques' => 'array',
        'dateSinistre' => 'date',
    ];
}
