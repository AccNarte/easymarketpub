<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Purchase;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use App\Models\User;



class ReviewController extends Controller
{
    /**
     * Créer une review pour une purchase
     */
    public function store(StoreReviewRequest $request, Purchase $purchase): JsonResponse
    {
        $review = Review::create([
            'purchase_id' => $purchase->id,
            'reviewer_id' => $request->user()->id,
            'reviewed_user_id' => $purchase->seller_id,
            'rating_overall' => $request->validated('rating_overall'),
            'rating_communication' => $request->validated('rating_communication'),
            'rating_product_state' => $request->validated('rating_product_state'),
            'rating_shipping_speed' => $request->validated('rating_shipping_speed'),
            'comment' => $request->validated('comment'),
            'review_deadline' => $purchase->updated_at->addDays(7),
        ]);

        return response()->json([
            'message' => 'Avis publié avec succès.',
            'review' => $review->load(['reviewer', 'reviewedUser']),
        ], 201);

    }

        public function index(\App\Models\User $user): \Illuminate\Http\JsonResponse
        {
            $reviews = $user->reviewsReceived()
                ->where('status', 'published')
                ->with(['reviewer:id,name', 'purchase:id,listing_id,status'])
                ->latest()
                ->paginate(10);
        
            return response()->json($reviews);
        }
    }
