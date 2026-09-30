<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Payment, Order, Rental};
use App\Services\IntouchPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(protected IntouchPaymentService $intouch) {}

    public function initiateMobileMoney(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required_without:rental_id|nullable|exists:orders,id',
            'rental_id' => 'required_without:order_id|nullable|exists:rentals,id',
            'operateur' => 'required|in:orange,mtn,moov,wave',
            'telephone' => 'required|string',
        ]);

        $montant = $data['order_id']
            ? Order::findOrFail($data['order_id'])->total
            : Rental::findOrFail($data['rental_id'])->montant_total;

        $referenceClient = 'MSDS-' . Str::upper(Str::random(10));

        $payment = Payment::create([
            'order_id' => $data['order_id'] ?? null,
            'rental_id' => $data['rental_id'] ?? null,
            'montant' => $montant,
            'methode' => 'mobile_money',
            'operateur' => $data['operateur'],
            'telephone' => $data['telephone'],
            'statut' => 'en_attente',
            'reference_client' => $referenceClient,
        ]);

        try {
            $response = $this->intouch->initierPaiement(
                $data['operateur'],
                $data['telephone'],
                $montant,
                $referenceClient
            );

            $payment->update(['reference_intouch' => $response['numTransaction'] ?? null]);

            return response()->json([
                'payment_id' => $payment->id,
                'statut' => 'en_attente',
                'message' => 'Confirmez le paiement sur votre téléphone.',
            ], 201);
        } catch (\Throwable $e) {
            $payment->update(['statut' => 'echoue']);
            return response()->json(['message' => 'Erreur lors de l\'initiation du paiement.'], 500);
        }
    }

    public function checkStatus(Payment $payment)
    {
        return response()->json(['statut' => $payment->statut]);
    }

    public function callback(Request $request)
    {
        $payload = $request->all();

        $payment = Payment::where('reference_client', $payload['idFromClient'] ?? null)->first();

        if (!$payment) {
            return response()->json(['message' => 'Paiement introuvable'], 404);
        }

        $statut = ($payload['status'] ?? '') === 'SUCCESS' ? 'reussi' : 'echoue';

        $payment->update([
            'statut' => $statut,
            'payload_retour' => $payload,
        ]);

        if ($statut === 'reussi') {
            if ($payment->order_id) {
                $payment->order->update(['statut' => 'paye']);
            }
            if ($payment->rental_id) {
                $payment->rental->update(['statut' => 'en_cours']);
            }
        }

        return response()->json(['message' => 'OK']);
    }
}