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
        $broadcast = function (Product $product) {
            // Un fallo de Reverb no debe romper el guardado; las pantallas
            // igual refrescan por el fallback de polling.
            try {
                MenuUpdated::dispatch($product->company_id);
            } catch (\Throwable $e) {
                report($e);
            }
        };

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
