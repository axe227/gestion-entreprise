<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'numero_facture',
        'client_id',
        'user_id',
        'date_facture',
        'date_echeance',
        'total_ht',
        'tva',
        'total_ttc',
        'statut',
        'statut_dgi',           // ✅ ajout
        'commentaire_dgi',      // ✅ ajout
        'date_validation_dgi',  // ✅ ajout
    ];

    protected $casts = [
        'date_facture'         => 'date',
        'date_echeance'        => 'date',
        'date_validation_dgi'  => 'datetime',
        'total_ht'             => 'float',
        'tva'                  => 'float',
        'total_ttc'            => 'float',
    ];

    public function client()   { return $this->belongsTo(Client::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function lignes()   { return $this->hasMany(LigneFacture::class); }
    public function paiements(){ return $this->hasMany(Paiement::class); }
}