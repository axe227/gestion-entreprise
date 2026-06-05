<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        return response()->json(
            Client::where('user_id', $userId)
                  ->withCount('factures')
                  ->withSum('factures', 'total_ttc')
                  ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'   => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
        ]);

        $client = Client::create([
            'user_id'    => auth()->id(), 
            'nom'        => $request->nom,
            'email'      => $request->email,
            'telephone'  => $request->telephone,
            'adresse'    => $request->adresse,
            'entreprise' => $request->entreprise,
            'statut'     => $request->statut ?? 'actif',
        ]);

        return response()->json($client, 201);
    }

    public function show($id)
    {
        $client = Client::where('user_id', auth()->id())
                        ->withCount('factures')
                        ->withSum('factures', 'total_ttc')
                        ->findOrFail($id);

        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = Client::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'email' => 'sometimes|email|unique:clients,email,' . $id,
        ]);

        $client->update([
            'nom'        => $request->nom        ?? $client->nom,
            'email'      => $request->email      ?? $client->email,
            'telephone'  => $request->telephone  ?? $client->telephone,
            'adresse'    => $request->adresse    ?? $client->adresse,
            'entreprise' => $request->entreprise ?? $client->entreprise,
            'statut'     => $request->statut     ?? $client->statut,
        ]);

        return response()->json($client);
    }

    public function destroy($id)
    {
        $client = Client::where('user_id', auth()->id())->findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Client supprimé']);
    }

    public function stats()
    {
        $userId = auth()->id();

        return response()->json([
            'total'  => Client::where('user_id', $userId)->count(),
            'actifs' => Client::where('user_id', $userId)->where('statut', 'actif')->count(),
            'revenu' => \App\Models\Facture::where('user_id', $userId)->sum('total_ttc'),
        ]);
    }
}