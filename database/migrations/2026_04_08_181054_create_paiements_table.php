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
    Schema::create('paiements', function (Blueprint $table) {
        $table->id();

        $table->foreignId('facture_id')->constrained()->onDelete('cascade');

        $table->decimal('montant', 12, 2);
        $table->date('date_paiement');

        $table->enum('methode', ['cash', 'virement', 'mobile_money', 'autre'])
              ->default('cash');

        $table->text('notes')->nullable();

        $table->timestamps();
        $table->enum('statut', ['en_attente', 'valide', 'refuse'])
      ->default('en_attente');          
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
