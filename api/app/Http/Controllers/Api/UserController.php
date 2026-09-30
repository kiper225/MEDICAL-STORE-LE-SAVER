<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->role, fn($q, $role) => $q->where('role', $role))
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'sometimes|in:client,vendeur,technicien,admin',
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}