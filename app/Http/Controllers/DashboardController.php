<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Produit;
use App\Models\Facture;
use App\Models\Paiement;

class DashboardController extends Controller
{
    public function stats()
    {
        $userId = auth()->id(); // ✅ Toutes les données filtrées par user

        $totalClients  = Client::where('user_id', $userId)->count();
        $totalProduits = Produit::where('user_id', $userId)->count();
        $totalFactures = Facture::where('user_id', $userId)->count();

        $chiffreAffaire = Facture::where('user_id', $userId)->sum('total_ttc');

        // Paiements liés aux factures de cet user
        $totalPaye   = Paiement::whereHas('facture', fn($q) => $q->where('user_id', $userId))->sum('montant');
        $resteAPayer = $chiffreAffaire - $totalPaye;

        $facturesPayees    = Facture::where('user_id', $userId)->where('statut', 'payee')->count();
        $facturesBrouillon = Facture::where('user_id', $userId)->where('statut', 'brouillon')->count();
        $facturesEnAttente = Facture::where('user_id', $userId)
                                    ->where('statut', '!=', 'payee')
                                    ->where('statut', '!=', 'brouillon')->count();

        $topClient = Client::where('user_id', $userId)
            ->withSum('factures', 'total_ttc')
            ->orderByDesc('factures_sum_total_ttc')->first();

        $topProduit = Produit::where('user_id', $userId)
            ->withSum('lignesFactures', 'quantite')
            ->orderByDesc('lignes_factures_sum_quantite')->first();

        // CA par mois
        $caParMois = Facture::where('user_id', $userId)
            ->selectRaw('MONTH(date_facture) as mois, SUM(total_ttc) as total')
            ->whereYear('date_facture', now()->year)
            ->groupBy('mois')->pluck('total', 'mois');

        $caData = [];
        for ($i = 1; $i <= 12; $i++) {
            $caData[] = isset($caParMois[$i]) ? (float) $caParMois[$i] : 0;
        }

        // Paiements par mois
        $paiementsParMois = Paiement::whereHas('facture', fn($q) => $q->where('user_id', $userId))
            ->selectRaw('MONTH(date_paiement) as mois, SUM(montant) as total')
            ->whereYear('date_paiement', now()->year)
            ->groupBy('mois')->pluck('total', 'mois');

        $paiementsData = [];
        for ($i = 1; $i <= 12; $i++) {
            $paiementsData[] = isset($paiementsParMois[$i]) ? (float) $paiementsParMois[$i] : 0;
        }

        // Alertes
        $alertes = [];
        $facturesImpayees = Facture::where('user_id', $userId)->where('statut', '!=', 'payee')->count();
        if ($facturesImpayees > 0) {
            $alertes[] = ['type' => 'danger', 'message' => $facturesImpayees . ' factures impayées'];
        }
        if ($resteAPayer > 0) {
            $alertes[] = ['type' => 'warning', 'message' => 'Reste à payer : ' . number_format($resteAPayer, 0, ',', ' ') . ' DT'];
        }

        $dernieresFactures = Facture::where('user_id', $userId)->with('client')->latest()->take(5)->get();
        $derniersPaiements = Paiement::whereHas('facture', fn($q) => $q->where('user_id', $userId))
                                     ->with('facture')->latest()->take(5)->get();

        return response()->json([
            'kpi' => [
                'clients'         => $totalClients,
                'produits'        => $totalProduits,
                'factures'        => $totalFactures,
                'chiffre_affaire' => $chiffreAffaire,
                'total_paye'      => $totalPaye,
                'reste_a_payer'   => $resteAPayer,
            ],
            'charts'   => ['ca' => $caData, 'paiements' => $paiementsData],
            'factures' => ['payees' => $facturesPayees, 'en_attente' => $facturesEnAttente, 'brouillon' => $facturesBrouillon],
            'top'      => ['client' => $topClient, 'produit' => $topProduit],
            'alertes'  => $alertes,
            'recent'   => ['factures' => $dernieresFactures, 'paiements' => $derniersPaiements],
        ]);
    }
}