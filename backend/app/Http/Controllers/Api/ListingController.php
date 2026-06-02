<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function index(Request $request)
    {
        $query = Listing::with(['user', 'category', 'images'])
            ->active()
            ->search($request->q);

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortField = match($request->sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            default => ['created_at', 'desc'],
        };

        $query->orderBy(...$sortField);

        $perPage = min($request->per_page ?? 12, 50);

        return response()->json($query->paginate($perPage));
    }

    public function show(Listing $listing)
    {
        $listing->incrementViews();
        $listing->load(['user', 'category', 'images']);

        return response()->json($listing);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'location' => 'required|string|max:255',
            'images' => 'array|max:10',
            'images.*' => 'image|max:10240', // 10MB max
        ]);

        /** @var Listing $listing */
        $listing = $request->user()->listings()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $position => $image) {
                $path = $image->store('listings/' . $listing->id, 'public');

                /** @var \App\Models\ListingImage $listingImage */
                $listingImage = $listing->images()->create([
                    'original_path' => $path,
                    'is_primary' => $position === 0,
                    'order' => $position,
                ]);

                // Send to image service for processing
                $this->imageService->processImage($listingImage);
            }
        }

        return response()->json($listing->load('images'), 201);
    }

    public function update(Request $request, Listing $listing)
    {
        $this->authorize('update', $listing);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,sold,inactive',
        ]);

        $listing->update($validated);

        return response()->json($listing->load(['user', 'category', 'images']));
    }

    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        // Delete images from storage
        foreach ($listing->images as $image) {
            Storage::disk('public')->delete($image->original_path);
            if ($image->thumbnail_path) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
            if ($image->medium_path) {
                Storage::disk('public')->delete($image->medium_path);
            }
        }

        $listing->delete();

        return response()->json(null, 204);
    }

    public function userListings(Request $request)
    {
        $listings = $request->user()
            ->listings()
            ->with(['category', 'images'])
            ->latest()
            ->get();

        return response()->json($listings);
    }
}
