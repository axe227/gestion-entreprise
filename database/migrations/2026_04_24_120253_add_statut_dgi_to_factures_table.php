<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            // ✅ Statut DGI séparé du statut paiement
            $table->enum('statut_dgi', ['pending', 'validated', 'rejected'])
                  ->default('pending')
                  ->after('statut');

            // ✅ Commentaire du DGI lors du rejet
            $table->text('commentaire_dgi')->nullable()->after('statut_dgi');

            // ✅ Date de validation/rejet
            $table->timestamp('date_validation_dgi')->nullable()->after('commentaire_dgi');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['statut_dgi', 'commentaire_dgi', 'date_validation_dgi']);
        });
    }
};