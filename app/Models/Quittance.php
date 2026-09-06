<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quittance extends Model
{
    protected $fillable = [
        'numeroQuittance',
        'montantVerse',
        'modeReglement',
        'beneficiaire',
        'sinistre_id',
    ];

    // Une quittance appartient à un sinistre (FK sinistre_id dans quittances)
    public function sinistre()
    {
        return $this->belongsTo(Sinistre::class);
    }

    // Une quittance peut avoir plusieurs documents (FK quittance_id dans documents)
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
