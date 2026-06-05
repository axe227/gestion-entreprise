<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Illuminate\Http\Request;

class DgiController extends Controller
{
    // ✅ Stats dashboard DGI
    public function stats()
    {
        $total     = Facture::count();
        $validated = Facture::where('statut_dgi', 'validated')->count();
        $rejected  = Facture::where('statut_dgi', 'rejected')->count();
        $pending   = Facture::where('statut_dgi', 'pending')->count();

        // Activité récente
        $recent = Facture::with('client', 'user')
            ->whereIn('statut_dgi', ['validated', 'rejected'])
            ->latest('date_validation_dgi')
            ->take(5)
            ->get()
            ->map(function ($f) {
                return [
                    'action'         => $f->statut_dgi === 'validated' ? 'validated' : 'rejected',
                    'numero_facture' => $f->numero_facture,
                    'date'           => $f->date_validation_dgi,
                ];
            });

        return response()->json([
            'total'     => $total,
            'validated' => $validated,
            'rejected'  => $rejected,
            'pending'   => $pending,
            'recent'    => $recent,
        ]);
    }

    // ✅ Liste toutes les factures (avec filtres)
    public function index(Request $request)
    {
        $query = Facture::with('client', 'user', 'lignes');

        // Filtres
        if ($request->search) {
            $query->where('numero_facture', 'like', '%' . $request->search . '%');
        }
        if ($request->statut_dgi && $request->statut_dgi !== 'all') {
            $query->where('statut_dgi', $request->statut_dgi);
        }
        if ($request->date_from) {
            $query->where('date_facture', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('date_facture', '<=', $request->date_to);
        }
        if ($request->company) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->company . '%')
                  ->orWhere('entreprise', 'like', '%' . $request->company . '%');
            });
        }
        if ($request->min_amount) {
            $query->where('total_ttc', '>=', $request->min_amount);
        }
        if ($request->max_amount) {
            $query->where('total_ttc', '<=', $request->max_amount);
        }

        $factures = $query->latest()->get();

        return response()->json($factures);
    }

    // ✅ Valider une facture
    public function validate($id)
    {
        $facture = Facture::findOrFail($id);

        $facture->update([
            'statut_dgi'           => 'validated',
            'commentaire_dgi'      => null,
            'date_validation_dgi'  => now(),
        ]);

        return response()->json([
            'message' => 'Facture validée avec succès',
            'facture' => $facture
        ]);
    }

    // ✅ Rejeter une facture
    public function reject(Request $request, $id)
    {
        $request->validate([
            'commentaire' => 'required|string|min:5'
        ]);

        $facture = Facture::findOrFail($id);

        $facture->update([
            'statut_dgi'           => 'rejected',
            'commentaire_dgi'      => $request->commentaire,
            'date_validation_dgi'  => now(),
        ]);

        return response()->json([
            'message' => 'Facture rejetée',
            'facture' => $facture
        ]);
    }

    // ✅ Détail d'une facture
    public function show($id)
    {
        return response()->json(
            Facture::with('client', 'user', 'lignes', 'paiements')->findOrFail($id)
        );
    }
}