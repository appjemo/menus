<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slot extends Model
{
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
