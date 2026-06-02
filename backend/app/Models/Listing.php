<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property float $price
 * @property string $location
 * @property string $status
 * @property int|null $buyer_id
 * @property \Illuminate\Support\Carbon|null $sold_at
 * @property int $views
 * @property-read User $user
 * @property-read User|null $buyer
 * @property-read Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ListingImage> $images
 */
class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'location',
        'status',
        'buyer_id',
        'sold_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    protected $appends = ['thumbnail'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('order');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function getThumbnailAttribute(): ?string
    {
        /** @var ListingImage|null $primaryImage */
        $primaryImage = $this->images()->where('is_primary', true)->first();
        if ($primaryImage) {
            return $primaryImage->thumbnail_url;
        }

        /** @var ListingImage|null $first */
        $first = $this->images()->first();
        return $first?->thumbnail_url;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        // PostgreSQL full-text search or SQLite LIKE fallback
        if ($query->getConnection()->getDriverName() === 'pgsql') {
            return $query->whereRaw(
                "to_tsvector('french', title || ' ' || description) @@ plainto_tsquery('french', ?)",
                [$term]
            );
        }

        // SQLite fallback
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
