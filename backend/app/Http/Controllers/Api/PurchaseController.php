<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        $user = $request->user();

        if ($listing->user_id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas acheter votre propre annonce'], 422);
        }

        if ($listing->status !== 'active' || $listing->sold_at) {
            return response()->json(['message' => 'Cette annonce n\'est plus disponible'], 422);
        }

        if ($user->balance < $listing->price) {
            return response()->json(['message' => 'Solde insuffisant'], 422);
        }

        $purchase = $this->processPurchase($user, $listing);

        return response()->json([
            'message' => 'Achat effectué avec succès',
            'purchase' => $purchase->load(['listing', 'seller']),
        ]);
    }

    

    protected function processPurchase($user, Listing $listing): Purchase
    {
        return DB::transaction(function () use ($user, $listing) {
            $price = (float) $listing->price;

            $user->withdraw($price, null, "Achat: {$listing->title}");
            $listing->user->deposit($price, null, "Vente: {$listing->title}");

            $purchase = Purchase::create([
                'listing_id' => $listing->id,
                'buyer_id' => $user->id,
                'seller_id' => $listing->user_id,
                'amount' => $price,
                'status' => 'completed',
            ]);

            $listing->update([
                'status' => 'sold',
                'buyer_id' => $user->id,
                'sold_at' => now(),
            ]);

            return $purchase;
        });
    }

    public function index(Request $request)
    {
        $purchases = Purchase::with(['listing.images', 'seller'])
            ->where('buyer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($purchases);
    }

    public function sales(Request $request)
    {
        $sales = Purchase::with(['listing.images', 'buyer'])
            ->where('seller_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($sales);
    }

    public function markShipped(Request $request, Purchase $purchase)
    {
        if ($purchase->seller_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $purchase->update([
            'shipping_status' => Purchase::SHIPPING_SHIPPED,
            'shipped_at' => now(),
        ]);

        return response()->json($purchase->fresh(['listing.images', 'buyer']));
    }

    public function markDelivered(Request $request, Purchase $purchase)
    {
        if ($purchase->buyer_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $purchase->update([
            'shipping_status' => Purchase::SHIPPING_DELIVERED,
            'delivered_at' => now(),
        ]);

        return response()->json($purchase->fresh(['listing.images', 'seller']));
    }
}
