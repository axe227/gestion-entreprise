<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DgiController;
use App\Http\Controllers\AdminController;

// ─────────────────────────────────────
// 🔓 PUBLIC
// ─────────────────────────────────────
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::get('public/stats', function () {
    return response()->json([
        'total_users'    => \App\Models\User::where('role', 'user')->count(),
        'total_factures' => \App\Models\Facture::count(),
        'total_revenue'  => \App\Models\Facture::sum('total_ttc'),
    ]);
});

Route::get('factures/{id}/public', function ($id) {
    $facture = \App\Models\Facture::with(['client', 'lignes'])->findOrFail($id);
    return response()->json([
        'numero_facture'  => $facture->numero_facture,
        'date_facture'    => $facture->date_facture,
        'date_echeance'   => $facture->date_echeance,
        'statut'          => $facture->statut,
        'statut_dgi'      => $facture->statut_dgi,
        'commentaire_dgi' => $facture->commentaire_dgi,
        'total_ht'        => $facture->total_ht,
        'tva'             => $facture->tva,
        'total_ttc'       => $facture->total_ttc,
        'client' => [
            'nom'        => $facture->client->nom,
            'email'      => $facture->client->email,
            'entreprise' => $facture->client->entreprise,
            'telephone'  => $facture->client->telephone,
            'adresse'    => $facture->client->adresse,
        ],
        'lignes' => $facture->lignes->map(fn($l) => [
            'designation'   => $l->designation,
            'quantite'      => $l->quantite,
            'prix_unitaire' => $l->prix_unitaire,
            'sous_total'    => $l->sous_total,
        ])
    ]);
});

// ─────────────────────────────────────
// 🔒 PROTÉGÉES (auth:sanctum)
// ─────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout',          [AuthController::class, 'logout']);
    Route::get('me',               [AuthController::class, 'me']);
    Route::put('profile/update',   [AuthController::class, 'updateProfile']);
    Route::put('profile/password', [AuthController::class, 'updatePassword']);

    Route::get('dashboard/stats',        [DashboardController::class, 'stats']);
    Route::get('dashboard/charts',       [DashboardController::class, 'charts']);
    Route::get('dashboard/top-clients',  [DashboardController::class, 'topClients']);
    Route::get('dashboard/top-produits', [DashboardController::class, 'topProduits']);

    Route::get('clients/stats', [ClientController::class, 'stats']);
    Route::apiResource('clients', ClientController::class);

    Route::get('produits/stats', [ProduitController::class, 'produitsStats']);
    Route::apiResource('produits', ProduitController::class);

    Route::get('factures/{id}/pdf', [FactureController::class, 'generatePDF']);
    Route::apiResource('factures', FactureController::class);

    Route::get('paiements',              [PaiementController::class, 'index']);
    Route::post('paiements',             [PaiementController::class, 'store']);
    Route::put('paiements/{id}/valider', [PaiementController::class, 'valider']);

    Route::get('tasks/alerts',        [TaskController::class, 'alerts']);
    Route::put('tasks/{id}/complete', [TaskController::class, 'complete']);
    Route::apiResource('tasks', TaskController::class);

    Route::prefix('dgi')->group(function () {
        Route::get('stats',                   [DgiController::class, 'stats']);
        Route::get('factures',                [DgiController::class, 'index']);
        Route::get('factures/{id}',           [DgiController::class, 'show']);
        Route::post('factures/{id}/validate', [DgiController::class, 'validate']);
        Route::post('factures/{id}/reject',   [DgiController::class, 'reject']);
    });

    Route::prefix('admin')->group(function () {
        Route::get('stats',            [AdminController::class, 'stats']);
        Route::get('users',            [AdminController::class, 'users']);
        Route::post('users',           [AdminController::class, 'createUser']);
        Route::put('users/{id}',       [AdminController::class, 'updateUser']);
        Route::delete('users/{id}',    [AdminController::class, 'deleteUser']);
        Route::get('factures',         [AdminController::class, 'factures']);
        Route::get('paiements',        [AdminController::class, 'paiements']);
        Route::get('activity',         [AdminController::class, 'activityLogs']);
    });

    Route::post('profile/avatar',   [AuthController::class, 'uploadAvatar']);
    Route::delete('profile/avatar', [AuthController::class, 'deleteAvatar']);
});