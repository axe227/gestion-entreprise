<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ✅ Vérifie si l'utilisateur est super_admin
    private function isSuperAdmin(Request $request): bool
    {
        return $request->user()?->role === 'super_admin';
    }

    // ✅ Stats dashboard
    public function stats(Request $request)
    {
        // Admin normal voit moins de stats
        $base = [
            'total_users'     => User::whereIn('role', ['user'])->count(),
            'total_clients'   => Client::count(),
            'total_revenue'   => Facture::sum('total_ttc'),
            'total_factures'  => Facture::count(),
        ];

        // Super admin voit tout
        if ($this->isSuperAdmin($request)) {
            $base['total_users']     = User::count();
            $base['total_paiements'] = Paiement::count();
            $base['users_by_role']   = [
                'user'        => User::where('role', 'user')->count(),
                'admin'       => User::where('role', 'admin')->count(),
                'dgi'         => User::where('role', 'dgi')->count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
            ];
        }

        return response()->json($base);
    }

    // ✅ Liste utilisateurs selon le rôle
    public function users(Request $request)
    {
        $query = User::query();

        // Admin normal → voit seulement les users
        if (!$this->isSuperAdmin($request)) {
            $query->where('role', 'user');
        }
        // Super admin → voit tout sauf les autres super_admins
        else {
            $query->where('role', '!=', 'super_admin')
                  ->orWhere('id', $request->user()->id);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name',  'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->role && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        return response()->json($query->latest()->get());
    }

    // ✅ Créer utilisateur
    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:user,admin,dgi,super_admin'
        ]);

        // Admin normal → peut créer seulement des users
        if (!$this->isSuperAdmin($request) &&
            in_array($request->role, ['admin', 'dgi', 'super_admin'])) {
            return response()->json(['error' => 'Permission insuffisante'], 403);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role
        ]);

        return response()->json($user, 201);
    }

    // ✅ Modifier utilisateur
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Admin normal → ne peut pas modifier admins/dgi/super_admin
        if (!$this->isSuperAdmin($request) &&
            in_array($user->role, ['admin', 'dgi', 'super_admin'])) {
            return response()->json(['error' => 'Permission insuffisante'], 403);
        }

        $request->validate([
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'role'  => 'sometimes|in:user,admin,dgi,super_admin'
        ]);

        // Admin normal → ne peut pas changer le rôle vers admin/dgi
        if (!$this->isSuperAdmin($request) &&
            isset($request->role) &&
            in_array($request->role, ['admin', 'dgi', 'super_admin'])) {
            return response()->json(['error' => 'Permission insuffisante'], 403);
        }

        $data = $request->only(['name', 'email', 'role']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json($user);
    }

    // ✅ Supprimer utilisateur
    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Protections
        if ($user->id === $request->user()->id) {
            return response()->json(['error' => 'Impossible de se supprimer soi-même'], 403);
        }
        if (in_array($user->role, ['super_admin', 'admin']) && !$this->isSuperAdmin($request)) {
            return response()->json(['error' => 'Permission insuffisante'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }

    // ✅ Toutes les factures
    public function factures()
    {
        return response()->json(
            Facture::with(['client', 'user'])->latest()->get()
        );
    }

    // ✅ Tous les paiements
    public function paiements()
    {
        return response()->json(
            Paiement::with('facture.client')->latest()->get()
        );
    }

    // ✅ Activity logs
    public function activityLogs()
    {
        $logs = collect();

        User::latest()->take(5)->get()->each(function($u) use (&$logs) {
            $logs->push([
                'type'  => 'user_created',
                'icon'  => 'user-plus',
                'color' => 'green',
                'title' => 'User Created',
                'desc'  => 'Created new user: '.$u->email,
                'by'    => 'Admin',
                'time'  => $u->created_at
            ]);
        });

        Facture::with('user')->latest()->take(5)->get()->each(function($f) use (&$logs) {
            $logs->push([
                'type'  => 'invoice_updated',
                'color' => 'blue',
                'title' => 'Invoice Created',
                'desc'  => 'Invoice '.$f->numero_facture.' created',
                'by'    => $f->user?->name ?? 'System',
                'time'  => $f->created_at
            ]);
        });

        Paiement::latest()->take(5)->get()->each(function($p) use (&$logs) {
            $logs->push([
                'type'  => 'payment',
                'color' => 'purple',
                'title' => 'Payment Processed',
                'desc'  => 'Payment of '.number_format($p->montant, 2).'€ completed',
                'by'    => 'System',
                'time'  => $p->created_at
            ]);
        });

        return response()->json(
            $logs->sortByDesc('time')->take(20)->values()
        );
    }
}