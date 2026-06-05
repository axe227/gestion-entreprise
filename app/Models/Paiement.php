<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'facture_id',
        'montant',
        'date_paiement',
        'methode',
        'notes',
        'statut'
    ];

    /**
     * Relation avec Facture
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Cast (important pour dates)
     */
    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2'
    ];
    
}