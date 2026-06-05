<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduitController extends Controller
{
    public function index()
    {
        return response()->json(
            Produit::where('user_id', auth()->id())->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'prix_unitaire' => 'required|numeric|min:0',
        ]);

        $produit = Produit::create([
            'user_id'       => auth()->id(), // ✅
            'nom'           => $request->nom,
            'description'   => $request->description,
            'prix_unitaire' => $request->prix_unitaire,
            'categorie'     => $request->categorie,
            'stock'         => $request->stock ?? 0,
        ]);

        return response()->json($produit, 201);
    }

    public function show($id)
    {
        return response()->json(
            Produit::where('user_id', auth()->id())->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $produit = Produit::where('user_id', auth()->id())->findOrFail($id);

        $produit->update([
            'nom'           => $request->nom           ?? $produit->nom,
            'description'   => $request->description   ?? $produit->description,
            'prix_unitaire' => $request->prix_unitaire ?? $produit->prix_unitaire,
            'categorie'     => $request->categorie     ?? $produit->categorie,
            'stock'         => $request->stock         ?? $produit->stock,
        ]);

        return response()->json($produit);
    }

    public function destroy($id)
    {
        $produit = Produit::where('user_id', auth()->id())->findOrFail($id);
        $produit->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }

    public function produitsStats()
    {
        $userId = auth()->id();

        $produits = Produit::where('user_id', $userId)
            ->withSum('lignesFactures', 'quantite')
            ->get()
            ->map(function ($p) {
                $p->revenu = (float) $p->lignesFactures()
                    ->sum(DB::raw('quantite * ' . (float) $p->prix_unitaire));
                return $p;
            });

        return response()->json([
            'kpi' => [
                'total_produits' => $produits->count(),
                'total_ventes'   => $produits->sum('lignes_factures_sum_quantite'),
                'revenu_total'   => $produits->sum('revenu'),
            ],
            'produits' => $produits,
        ]);
    }
}