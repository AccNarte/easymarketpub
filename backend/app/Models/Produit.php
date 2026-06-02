<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $public_id
 * @property string $nom
 * @property string|null $description
 * @property float $prix
 * @property string|null $image
 * @property string|null $image_url
 */
class Produit extends Model
{
    use HasFactory;

    protected $table = 'produits';
    protected $fillable = ['public_id', 'nom', 'description', 'prix', 'image'];

    protected static function booted(): void
    {
        static::creating(function ($produit) {
            if (empty($produit->public_id)) {
                do {
                    $publicId = Str::random(16);
                } while (self::where('public_id', $publicId)->exists());
                $produit->public_id = $publicId;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
