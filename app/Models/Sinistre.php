<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sinistre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dateSinistre',
        'description',
        'lieuSinistre',
        'statut',
        'type_id',
        'assure_id',
        'gestionnaire_id',
        'expert_id',
        'contrat_id',
    ];

    // protected $casts = [
    //     'dateSinistre' => 'date',
    // ];

    // Un sinistre appartient à un contrat (FK contrat_id dans sinistres)
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    // Un sinistre appartient à un assuré (FK assure_id dans sinistres)
    public function assure()
    {
        return $this->belongsTo(User::class, 'assure_id');
    }

    // Un sinistre est géré par un gestionnaire (FK gestionnaire_id dans sinistres)
    public function gestionnaire()
    {
        return $this->belongsTo(User::class, 'gestionnaire_id');
    }

    // Un sinistre est traité par un expert (FK expert_id dans sinistres)
    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    // Un sinistre a au plus une indemnisation (FK sinistre_id dans indemnisations)
    public function indemnisation()
    {
        return $this->hasOne(Indemnisation::class);
    }

    // Un sinistre a au plus une quittance (FK sinistre_id dans quittances)
    public function quittance()
    {
        return $this->hasOne(Quittance::class);
    }

    // Un sinistre peut avoir plusieurs documents (FK sinistre_id dans documents)
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
