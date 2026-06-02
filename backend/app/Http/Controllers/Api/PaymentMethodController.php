<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = $request->user()
            ->paymentMethods()
            ->orderByDesc('is_default')
            ->get();

        return response()->json($methods);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:card,bank,crypto',
            'provider' => 'required|string',
            'label' => 'required|string|max:100',
            'details' => 'nullable|array',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        // If this is the first payment method, make it default
        if ($user->paymentMethods()->count() === 0) {
            $validated['is_default'] = true;
        }

        $method = $user->paymentMethods()->create($validated);

        return response()->json($method, 201);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        // Verify ownership
        if ($paymentMethod->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Check if there are pending transactions
        if ($paymentMethod->transactions()->where('status', 'pending')->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer : transactions en cours'
            ], 422);
        }

        $paymentMethod->delete();

        return response()->json(null, 204);
    }

    public function setDefault(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->user()->paymentMethods()->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return response()->json($paymentMethod);
    }
}
