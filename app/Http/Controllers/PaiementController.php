<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Facture;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $paiements = Paiement::whereHas('facture', fn($q) => $q->where('user_id', $userId))
            ->with(['facture.client'])
            ->latest()
            ->get()
            ->map(function($p) {
                // ✅ Statut basé sur si la facture est payée ET montant = total
                $facture     = $p->facture;
                $totalPaye   = $facture?->paiements()->sum('montant') ?? 0;
                $p->statut   = ($facture?->statut === 'payee') ? 'complete' : 'en_attente';
                return $p;
            });

        return response()->json($paiements);
    }

    public function store(Request $request)
    {
        $request->validate([
            'facture_id'    => 'required|exists:factures,id',
            'montant'       => 'required|numeric|min:1',
            'date_paiement' => 'required|date',
            'methode'       => 'required|string'
        ]);

        $facture = Facture::where('user_id', auth()->id())
                          ->findOrFail($request->facture_id);

        $totalPaye    = $facture->paiements()->sum('montant');
        $nouveauTotal = $totalPaye + $request->montant;

        if ($nouveauTotal > $facture->total_ttc) {
            return response()->json([
                'error'     => 'Le montant dépasse le total de la facture',
                'total_ttc' => $facture->total_ttc,
                'deja_paye' => $totalPaye
            ], 400);
        }

        $paiement = Paiement::create([
            'facture_id'    => $request->facture_id,
            'montant'       => $request->montant,
            'date_paiement' => $request->date_paiement,
            'methode'       => $request->methode,
            'notes'         => $request->notes ?? null
        ]);

        // ✅ Met à jour le statut de la facture
        if ($nouveauTotal >= $facture->total_ttc) {
            $facture->update(['statut' => 'payee']);
        } else {
            $facture->update(['statut' => 'en_attente']);
        }

        return response()->json([
            'message'    => 'Paiement enregistré',
            'paiement'   => $paiement->load('facture.client'),
            'total_paye' => $nouveauTotal
        ], 201);
    }
}