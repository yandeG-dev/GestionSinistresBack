<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'chemin_fichier',
        'type_document',
        'sinistre_id' // Le lien avec la table sinistre
    ];
}
