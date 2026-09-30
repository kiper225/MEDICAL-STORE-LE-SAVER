<?php

namespace App\Services;

use AlexNguetcha\Intouch\Intouch;

class IntouchPaymentService
{
    protected function client()
    {
        return Intouch::credentials(
            username: config('services.intouch.username'),
            password: config('services.intouch.password'),
            loginAgent: config('services.intouch.login_agent'),
            passwordAgent: config('services.intouch.password_agent'),
            intouchId: config('services.intouch.intouch_id'),
        )->callback(config('services.intouch.callback_url'));
    }

    public function initierPaiement(string $operateur, string $telephone, float $montant, string $referenceClient): array
    {
        $serviceCode = config("services.intouch.services.{$operateur}");

        if (!$serviceCode) {
            throw new \InvalidArgumentException("Opérateur non supporté : {$operateur}");
        }

        $response = $this->client()
            ->amount($montant)
            ->phone($telephone)
            ->operator(strtoupper($operateur))
            ->partnerId(config('services.intouch.partner_id'))
            ->makeMerchantPayment([
                'reason' => 'Commande Medical Store Dieu Sauveur',
                'idFromClient' => $referenceClient,
            ]);

        return (array) $response;
    }
}