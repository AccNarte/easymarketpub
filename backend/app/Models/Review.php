<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $purchase_id
 * @property int $reviewer_id
 * @property int $reviewed_user_id
 * @property int $rating_overall
 * @property int|null $rating_communication
 * @property int|null $rating_product_state
 * @property int|null $rating_shipping_speed
 * @property string|null $comment
 * @property string|null $seller_response
 * @property \Illuminate\Support\Carbon|null $seller_response_at
 * @property string $status
 * @property \Illuminate\Support\Carbon $review_deadline
 */
class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'reviewer_id',
        'reviewed_user_id',
        'rating_overall',
        'rating_communication',
        'rating_product_state',
        'rating_shipping_speed',
        'comment',
        'seller_response',
        'seller_response_at',
        'status',
        'review_deadline',
    ];

    protected $casts = [
        'rating_overall' => 'integer',
        'rating_communication' => 'integer',
        'rating_product_state' => 'integer',
        'rating_shipping_speed' => 'integer',
        'seller_response_at' => 'datetime',
        'review_deadline' => 'datetime',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
    
    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }
}