<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Produit extends Model
{
   protected $fillable = ['user_id', 'nom', 'description', 'prix_unitaire', 'categorie', 'stock'];
    // ✅ Alias "prix" → pour Angular
    public function getPrixAttribute(): float
    {
        return (float) $this->prix_unitaire;
    }

    // ✅ Revenu calculé
    public function getRevenuAttribute(): float
    {
        return (float) $this->lignesFactures()
            ->sum(DB::raw('quantite * ' . (float) $this->prix_unitaire));
    }

    protected $appends = ['prix', 'revenu'];

    public function lignesFactures()
    {
        return $this->hasMany(\App\Models\LigneFacture::class, 'produit_id');
    }
}