<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneFacture extends Model
{
    protected $fillable = [
        'facture_id',
        'produit_id',
        'designation',
        'quantite',
        'prix_unitaire',
        'sous_total'
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function produit()
{
    return $this->belongsTo(\App\Models\Produit::class);
}
}