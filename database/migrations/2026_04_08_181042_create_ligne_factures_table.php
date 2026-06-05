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
    Schema::create('ligne_factures', function (Blueprint $table) {
        $table->id();

        $table->foreignId('facture_id')->constrained()->onDelete('cascade');
        $table->foreignId('produit_id')->nullable()->constrained()->onDelete('set null');

        $table->string('designation');
        $table->integer('quantite');
        $table->decimal('prix_unitaire', 10, 2);
        $table->decimal('tva', 5, 2)->default(0);
        $table->decimal('sous_total', 12, 2);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_factures');
    }
};
