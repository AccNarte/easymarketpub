<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['category', 'images'])
            ->get();

        return response()->json($favorites);
    }

    public function toggle(Request $request, Listing $listing)
    {
        $user = $request->user();
        $exists = $user->favorites()->where('listing_id', $listing->id)->exists();

        if ($exists) {
            $user->favorites()->detach($listing->id);
            $message = 'Retiré des favoris';
        } else {
            $user->favorites()->attach($listing->id);
            $message = 'Ajouté aux favoris';
        }

        return response()->json([
            'is_favorite' => !$exists,
            'message' => $message,
        ]);
    }
}
