<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'balance' => $user->balance,
            'formatted_balance' => number_format($user->balance, 2, ',', ' ') . ' €',
        ]);
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        /** @var User $user */
        $user = $request->user();

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $user->paymentMethods()->findOrFail($validated['payment_method_id']);

        $transaction = $user->deposit(
            (float) $validated['amount'],
            $paymentMethod,
            "Rechargement via {$paymentMethod->label}"
        );

        return response()->json([
            'message' => 'Rechargement effectué avec succès',
            'transaction' => $transaction,
            'new_balance' => $user->fresh()->balance,
        ]);
    }

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->balance < $validated['amount']) {
            return response()->json([
                'message' => 'Solde insuffisant',
                'balance' => $user->balance,
            ], 422);
        }

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $user->paymentMethods()->findOrFail($validated['payment_method_id']);

        $transaction = $user->withdraw(
            (float) $validated['amount'],
            $paymentMethod,
            "Retrait vers {$paymentMethod->label}"
        );

        return response()->json([
            'message' => 'Retrait effectué avec succès',
            'transaction' => $transaction,
            'new_balance' => $user->fresh()->balance,
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->with('paymentMethod')
            ->paginate(20);

        return response()->json($transactions);
    }

    public function providers()
    {
        return response()->json(PaymentMethod::providers());
    }
}
