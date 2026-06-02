<?php

namespace App\Models;

use App\Exceptions\InsufficientBalanceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property float $balance
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $banned_at
 * @property string|null $banned_reason
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'role',
        'banned_at',
        'banned_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id')
            ->orWhere('seller_id', $this->id);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function defaultPaymentMethod(): ?PaymentMethod
    {
        /** @var PaymentMethod|null $method */
        $method = $this->paymentMethods()->where('is_default', true)->first();
        return $method;
    }

    public function deposit(float $amount, ?PaymentMethod $method = null, ?string $description = null): Transaction
    {
        $balanceBefore = $this->balance;
        $this->increment('balance', $amount);

        /** @var Transaction $transaction */
        $transaction = $this->transactions()->create([
            'payment_method_id' => $method?->id,
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'description' => $description ?? 'Rechargement du solde',
        ]);

        return $transaction;
    }

    public function withdraw(float $amount, ?PaymentMethod $method = null, ?string $description = null): Transaction
    {
        if ($this->balance < $amount) {
            throw new InsufficientBalanceException();
        }

        $balanceBefore = $this->balance;
        $this->decrement('balance', $amount);

        /** @var Transaction $transaction */
        $transaction = $this->transactions()->create([
            'payment_method_id' => $method?->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'status' => 'completed',
            'description' => $description ?? 'Retrait',
        ]);

        return $transaction;
    }

    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewed_user_id');
    }

    public function reviewReports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }
}
