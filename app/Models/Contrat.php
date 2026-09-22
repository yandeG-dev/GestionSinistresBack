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
        'policeAssurance',
        'vehicule_id',
        'assure_id',
    ];

    public function assure()
    {
        return $this->belongsTo(User::class, 'assure_id');
    }

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }
}
