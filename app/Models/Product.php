<?php

namespace App\Models;

use App\Events\MenuUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected static function booted(): void
    {
        $broadcast = fn (Product $product) => MenuUpdated::dispatch($product->company_id);

        static::saved($broadcast);
        static::deleted($broadcast);
    }

    protected $fillable = [
        'company_id',
        'name',
        'price',
        'category',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }
}
