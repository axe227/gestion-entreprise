<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  protected $fillable = ['user_id', 'nom', 'email', 'telephone', 'adresse', 'entreprise', 'statut'];

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
}
