<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {

            // ✅ Ajoute colonne "prix" (copie de prix_unitaire)
            $table->decimal('prix', 10, 2)->default(0)->after('description');

            // ✅ Ajoute colonne "categorie"
            $table->string('categorie')->nullable()->after('prix');
        });

        // ✅ Copie les valeurs existantes de prix_unitaire vers prix
        DB::statement('UPDATE produits SET prix = prix_unitaire');
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['prix', 'categorie']);
        });
    }
};