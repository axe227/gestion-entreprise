<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── CLIENTS ──
        if (!Schema::hasColumn('clients', 'user_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade')
                      ->after('id');
            });
        }

        // ── PRODUITS ──
        if (!Schema::hasColumn('produits', 'user_id')) {
            Schema::table('produits', function (Blueprint $table) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade')
                      ->after('id');
            });
        }

        // ── TASKS ──
        if (!Schema::hasColumn('tasks', 'user_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained()
                      ->onDelete('cascade')
                      ->after('id');
            });
        }

        // ── PAIEMENTS ──
        // Paiement est lié à Facture qui est liée à user_id
        // Donc pas besoin de user_id direct sur paiements
        // On filtre via facture->user_id (déjà fait dans DashboardController)
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};