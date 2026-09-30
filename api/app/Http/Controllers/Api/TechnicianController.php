<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = User::where('role', 'technicien')
            ->withCount([
                'installationsAssignees as installations_en_cours' => function ($q) {
                    $q->whereIn('statut', ['planifiee', 'en_cours']);
                },
            ])
            ->get();

        return response()->json($technicians);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string',
        ]);

        $technician = User::create([
            'nom' => $data['nom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'technicien',
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? null,
        ]);

        return response()->json($technician, 201);
    }

    public function destroy(User $technician)
    {
        if ($technician->role !== 'technicien') {
            return response()->json(['message' => 'Cet utilisateur n\'est pas un technicien.'], 422);
        }

        $technician->delete();
        return response()->json(null, 204);
    }
}