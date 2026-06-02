<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()->with(['user:id,name,email', 'category:id,name']);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $listings = $query->latest()->paginate(20);

        return response()->json($listings);
    }

    public function show(Listing $listing)
    {
        $listing->load(['user', 'category', 'images', 'buyer']);
        return response()->json($listing);
    }

    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'status' => ['sometimes', 'required', Rule::in(['active', 'sold', 'inactive'])],
        ]);

        $listing->update($validated);

        return response()->json([
            'message' => 'Annonce mise à jour',
            'listing' => $listing->fresh(['user', 'category', 'images']),
        ]);
    }

    public function destroy(Listing $listing)
    {
        foreach ($listing->images as $image) {
            Storage::disk('public')->delete(array_filter([
                $image->original_path,
                $image->thumbnail_path,
                $image->medium_path,
            ]));
        }

        $listing->delete();

        return response()->json(['message' => 'Annonce supprimée']);
    }
}
