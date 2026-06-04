<?php

namespace App\Models;

use App\Events\MenuUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slot extends Model
{
    protected static function booted(): void
    {
        $broadcast = function (Slot $slot) {
            try {
                $companyId = $slot->template?->company_id;
                if ($companyId) {
                    MenuUpdated::dispatch($companyId);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        };

        static::saved($broadcast);
        static::deleted($broadcast);
    }

    protected $fillable = [
        'template_id',
        'product_id',
        'label',
        'pos_x',
        'pos_y',
        'font_size',
        'font_color',
        'font_family',
        'align',
        'show_name',
        'layout',
        'box_width',
    ];

    protected function casts(): array
    {
        return [
            'show_name' => 'boolean',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
