<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->renameColumn('prix_unitaire', 'prix');
            $table->string('categorie')->nullable()->after('prix');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->renameColumn('prix', 'prix_unitaire');
            $table->dropColumn('categorie');
        });
    }
};