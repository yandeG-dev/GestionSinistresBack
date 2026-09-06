<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numeroContrat',
        'typeContrat',
        'dateDebut',
        'dateFin',
        'franchise',
        'prime',
        'garantie',
        'vehicule_id',
        'assure_id',
    ];

    // protected $casts = [
    //     'dateDebut' => 'date',
    //     'dateFin' => 'date',
    //     'franchise' => 'decimal:2',
    //     'prime' => 'decimal:2',
    // ];

    // Un contrat appartient à un assuré (User)
    // La FK assure_id est dans la table contrats
    public function assure()
    {
        return $this->belongsTo(User::class, 'assure_id');
    }
    // Un contrat appartient à un véhicule
    // La FK vehicule_id est dans la table contrats
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    // Un contrat peut avoir plusieurs sinistres
    // La FK contrat_id est dans la table sinistres
    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }
}
