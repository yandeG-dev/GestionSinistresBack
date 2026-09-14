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
        'sinistre_id',
    ];

    /**
     * Un document appartient à un sinistre
     */
    public function sinistre()
    {
        return $this->belongsTo(Sinistre::class);
    }
}
