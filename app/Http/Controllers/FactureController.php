<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Facture;
use App\Models\LigneFacture;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class FactureController extends Controller
{
    public function index()
    {
        return response()->json(
            Facture::where('user_id', auth()->id())
                   ->with(['client', 'lignes', 'paiements'])
                   ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.designation'   => 'required|string|max:255',
            'lignes.*.quantite'      => 'required|numeric|min:1',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $facture = Facture::create([
            'numero_facture' => 'FAC-' . strtoupper(uniqid()),
            'client_id'      => $request->client_id,
            'user_id'        => auth()->id(),
            'date_facture'   => now(),
            'statut'         => 'brouillon',
            'statut_dgi'     => 'pending',
        ]);

        $total = 0;
        foreach ($request->lignes as $ligne) {
            $sous_total = $ligne['quantite'] * $ligne['prix_unitaire'];
            LigneFacture::create([
                'facture_id'    => $facture->id,
                'produit_id'    => $ligne['produit_id'] ?? null,
                'designation'   => $ligne['designation'],
                'quantite'      => $ligne['quantite'],
                'prix_unitaire' => $ligne['prix_unitaire'],
                'sous_total'    => $sous_total,
            ]);
            $total += $sous_total;
        }

        $tva = $total * 0.19;
        $facture->update([
            'total_ht'  => $total,
            'tva'       => $tva,
            'total_ttc' => $total + $tva,
        ]);

        return response()->json($facture->load('lignes'), 201);
    }

    public function show($id)
    {
        return response()->json(
            Facture::where('user_id', auth()->id())
                   ->with(['client', 'lignes', 'paiements'])
                   ->findOrFail($id)
        );
    }

    public function destroy($id)
    {
        $facture = Facture::where('user_id', auth()->id())->findOrFail($id);
        $facture->delete();
        return response()->json(['message' => 'Facture supprimée']);
    }

   public function generatePDF($id)
{
    $facture = Facture::where('user_id', auth()->id())
                      ->with(['client', 'lignes'])
                      ->findOrFail($id);
 
    // ✅ URL Angular Cloudflare pour le QR code
   $qrData = 'https://trials-trading-counted-precision.trycloudflare.com/verify/'. $facture->id;
 
    $qr     = \Endroid\QrCode\QrCode::create($qrData)->setSize(150);
    $writer = new \Endroid\QrCode\Writer\PngWriter();
    $qrCode = base64_encode($writer->write($qr)->getString());
 
    $pdf = Pdf::loadView('facture', compact('facture', 'qrCode'));
    return $pdf->stream('facture_' . $facture->numero_facture . '.pdf');
}
}