<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $fillable = [
        'marque',
        'modele',
        'immatriculation',
        'type_carburant',
        'puissance_fiscale',
        'date_mise_en_circulation',
        'contrat_id',
    ];
    // Un véhicule appartient à un contrat (FK contrat_id dans vehicules)
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }
}
