<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInstallationRequest;
use App\Models\Installation;
use App\Enums\InstallationStatus;
use Illuminate\Http\Request;

class InstallationController extends Controller
{
    public function index(Request $request)
    {
        $installations = Installation::with(['order.user', 'rental.user', 'rental.product', 'technicien'])
            ->when($request->user()->role === 'technicien', fn($q) =>
                $q->where('technicien_id', $request->user()->id))
            ->when($request->statut, fn($q, $statut) =>
                $q->where('statut', $statut))
            ->orderBy('date_prevue')
            ->paginate(20);

        return response()->json($installations);
    }

    public function myInstallations(Request $request)
    {
        $installations = Installation::with(['rental.product', 'order'])
            ->where('technicien_id', $request->user()->id)
            ->orderBy('date_prevue')
            ->paginate(20);

        return response()->json($installations);
    }

    public function store(StoreInstallationRequest $request)
    {
        $installation = Installation::create(array_merge(
            $request->validated(),
            ['statut' => InstallationStatus::Planifiee]
        ));

        return response()->json($installation->load(['order', 'rental']), 201);
    }

    public function show(Installation $installation)
    {
        return response()->json($installation->load(['order', 'rental', 'technicien']));
    }

    public function update(Request $request, Installation $installation)
    {
        $data = $request->validate([
            'technicien_id' => 'nullable|exists:users,id',
            'date_prevue' => 'sometimes|date',
            'creneau_horaire' => 'sometimes|string',
            'statut' => 'sometimes|string',
            'rapport_intervention' => 'nullable|string',
            'signature_client' => 'nullable|string',
        ]);

        $installation->update($data);

        return response()->json($installation);
    }

    public function destroy(Installation $installation)
    {
        $installation->update(['statut' => InstallationStatus::Annulee]);
        return response()->json(null, 204);
    }

    public function technicienDashboard(Request $request)
    {
        $techId = $request->user()->id;

        return response()->json([
            'total_installations' => Installation::where('technicien_id', $techId)->count(),
            'en_attente' => Installation::where('technicien_id', $techId)->whereIn('statut', ['planifiee', 'en_cours'])->count(),
            'terminees' => Installation::where('technicien_id', $techId)->where('statut', 'terminee')->count(),
        ]);
    }
}