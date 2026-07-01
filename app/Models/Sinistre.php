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
        'type_id', // Ce champ était déjà commenté en bdd, on le laisse s'il sert à rien ce n'est pas grave
        'assure_id',
        'contrat_id'
    ];

    protected $casts = [
        'dateSinistre' => 'date',
    ];
}
