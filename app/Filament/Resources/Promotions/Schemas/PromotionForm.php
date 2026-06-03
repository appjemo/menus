<?php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->default(null),
                TextInput::make('title')
                    ->required(),
                TextInput::make('promo_price')
                    ->numeric()
                    ->default(null)
                    ->prefix('$'),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
