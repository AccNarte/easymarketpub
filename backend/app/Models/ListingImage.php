<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $listing_id
 * @property string $original_path
 * @property string|null $thumbnail_path
 * @property string|null $medium_path
 * @property array|null $analysis
 * @property bool $is_primary
 * @property int $order
 * @property-read string $original_url
 * @property-read string|null $thumbnail_url
 * @property-read string|null $medium_url
 */
class ListingImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'original_path',
        'thumbnail_path',
        'medium_path',
        'analysis',
        'is_primary',
        'order',
    ];

    protected $casts = [
        'analysis' => 'array',
        'is_primary' => 'boolean',
    ];

    protected $appends = ['original_url', 'thumbnail_url', 'medium_url'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function getOriginalUrlAttribute(): string
    {
        return Storage::url($this->original_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : $this->original_url;
    }

    public function getMediumUrlAttribute(): ?string
    {
        return $this->medium_path ? Storage::url($this->medium_path) : $this->original_url;
    }
}
