<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'label',
        'details',
        'is_default',
    ];

    protected $casts = [
        'details' => 'encrypted:array',
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        'details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function providers(): array
    {
        return [
            'card' => [
                ['id' => 'visa', 'name' => 'Visa', 'icon' => 'visa'],
                ['id' => 'mastercard', 'name' => 'Mastercard', 'icon' => 'mastercard'],
                ['id' => 'amex', 'name' => 'American Express', 'icon' => 'amex'],
            ],
            'bank' => [
                ['id' => 'sepa', 'name' => 'Virement SEPA', 'icon' => 'bank'],
                ['id' => 'paypal', 'name' => 'PayPal', 'icon' => 'paypal'],
            ],
            'crypto' => [
                ['id' => 'bitcoin', 'name' => 'Bitcoin (BTC)', 'icon' => 'btc'],
                ['id' => 'ethereum', 'name' => 'Ethereum (ETH)', 'icon' => 'eth'],
                ['id' => 'usdt', 'name' => 'Tether (USDT)', 'icon' => 'usdt'],
                ['id' => 'usdc', 'name' => 'USD Coin (USDC)', 'icon' => 'usdc'],
            ],
        ];
    }
}
