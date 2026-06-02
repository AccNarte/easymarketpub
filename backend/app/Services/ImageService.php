<?php

namespace App\Services;

use App\Models\ListingImage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.image_service.url');
        $this->apiKey = config('services.image_service.api_key');
        $this->callbackUrl = config('services.image_service.callback_url');
    }

    public function processImage(ListingImage $image): bool
    {
        if (!$this->baseUrl || !$this->apiKey) {
            Log::warning('Image service not configured');
            return false;
        }

        try {
            $response = $this->sendToService($image);
        } catch (Throwable $e) {
            Log::error('Image service exception', [
                'image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }

        if ($response->successful()) {
            Log::info('Image sent for processing', ['image_id' => $image->id]);
            return true;
        }

        Log::error('Image service error', [
            'image_id' => $image->id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
        return false;
    }

    protected function sendToService(ListingImage $image): Response
    {
        $filePath = Storage::disk('public')->path($image->original_path);

        return Http::withHeaders(['X-API-Key' => $this->apiKey])
            ->attach('image', file_get_contents($filePath), basename($filePath))
            ->post($this->baseUrl . '/analyze', [
                'image_id' => $image->id,
                'callbackUrl' => $this->callbackUrl,
            ]);
    }
}
