<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('factures', function (Blueprint $table) {
         $table->id();
    $table->string('numero_facture')->unique();
    $table->foreignId('client_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->date('date_facture');
    $table->date('date_echeance')->nullable();
    $table->decimal('total_ht', 10, 2)->default(0);
    $table->decimal('tva', 10, 2)->default(0);
    $table->decimal('total_ttc', 10, 2)->default(0);
    $table->string('statut')->default('brouillon');
    $table->string('statut_dgi')->default('pending'); // 🔹 ajouté
    $table->text('commentaire_dgi')->nullable();
    $table->timestamp('date_validation_dgi')->nullable();
    $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
