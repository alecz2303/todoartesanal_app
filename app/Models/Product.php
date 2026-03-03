<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'dimensions',
        'price_cents',
        'stock',
        'is_active',
        'cover_image_path',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function getPriceMxAttribute(): string
    {
        return number_format($this->price_cents / 100, 2);
    }
}
