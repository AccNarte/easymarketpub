<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Purchase;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (!$this->user()) {
            return false;
        }

        $purchase = $this->getPurchase();
        if (!$purchase) {
            return false;
        }
        if ($purchase->buyer_id !== $this->user()->id) {
            return false;
        }
        
        if ($purchase->status !== 'completed') {
            return false;
        }

    return true;
    }



    public function rules(): array
    {
        return [
            'rating_overall' => 'required|integer|min:1|max:5',
            'rating_communication' => 'required|integer|min:1|max:5',
            'rating_product_state' => 'required|integer|min:1|max:5',
            'rating_shipping_speed' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ];
    }



public function withValidator(Validator $validator) : void
{
    $validator->after(function ($validator) {
        $purchase = $this->getPurchase();
        if (!$purchase) {
            return;
        }
        if ($purchase->review()->exists()) {
            $validator->errors()->add('purchase', 'Vous avez déjà laissé une review pour cette commande');
        }

        $deadline = $purchase->created_at->addDays(7);

        if (now()->gt($deadline)) {
            $validator->errors()->add('purchase', 'Le délai pour laisser une review a expiré');
        }
    });
}


public function messages(): array
    {
        return [
            'rating_overall.required' => 'La note globale est requise',
            'rating_overall.integer' => 'La note globale doit être un nombre entier',
            'rating_overall.min' => 'La note globale doit être au moins 1',
            'rating_overall.max' => 'La note globale doit être au plus 5',
            'rating_communication.required' => 'La note de communication est requise',
            'rating_communication.integer' => 'La note de communication doit être un nombre entier',
            'rating_communication.min' => 'La note de communication doit être au moins 1',
            'rating_communication.max' => 'La note de communication doit être au plus 5',
        ];
    }


    private function getPurchase() : ?Purchase
    {
        $purchaseId = $this->route('purchase');
        if (!$purchaseId) {
            return null;
        }
        if ($purchaseId instanceof Purchase) {
            return $purchaseId;
        }
        return Purchase::find($purchaseId);
        

    }

}