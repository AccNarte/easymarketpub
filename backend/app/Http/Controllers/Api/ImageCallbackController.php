<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Vérifie la clé d'API du service externe
        $apiKey = $request->header('X-API-Key');
        if ($apiKey !== config('services.image_service.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'image_id' => 'required|exists:listing_images,id',
            'analysis' => 'required|array',
            'thumbnail' => 'nullable|string', // Encodée en base64
            'medium' => 'nullable|string', // Encodée en base64
        ]);

        $image = ListingImage::findOrFail($validated['image_id']);

        // Sauvegarde de la miniature
        if (!empty($validated['thumbnail'])) {
            $thumbnailPath = 'listings/' . $image->listing_id . '/thumb_' . basename($image->original_path);
            Storage::disk('public')->put($thumbnailPath, base64_decode($validated['thumbnail']));
            $image->thumbnail_path = $thumbnailPath;
        }

        // Sauvegarde du format intermédiaire
        if (!empty($validated['medium'])) {
            $mediumPath = 'listings/' . $image->listing_id . '/med_' . basename($image->original_path);
            Storage::disk('public')->put($mediumPath, base64_decode($validated['medium']));
            $image->medium_path = $mediumPath;
        }

        $image->analysis = $validated['analysis'];
        $image->save();

        return response()->json(['status' => 'ok']);
    }
}
