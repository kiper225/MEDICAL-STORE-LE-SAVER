<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstallationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // géré plutôt via une Policy si besoin
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required_without:rental_id|nullable|exists:orders,id',
            'rental_id' => 'required_without:order_id|nullable|exists:rentals,id',
            'technicien_id' => 'nullable|exists:users,id',
            'adresse_installation' => 'required|string|max:255',
            'date_prevue' => 'required|date|after_or_equal:today',
            'creneau_horaire' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required_without' => 'Une installation doit être rattachée à une commande ou une location.',
            'rental_id.required_without' => 'Une installation doit être rattachée à une commande ou une location.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Empêche qu'on remplisse les deux en même temps
        if ($this->filled('order_id') && $this->filled('rental_id')) {
            $this->merge(['rental_id' => null]);
        }
    }
}