<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Models\Product;
use Filament\Facades\Filament;
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
                TextInput::make('title')
                    ->label('Promotion title')
                    ->required()
                    ->maxLength(255),
                Select::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::query()
                        ->where('company_id', Filament::getTenant()?->getKey())
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                TextInput::make('promo_price')
                    ->label('Promo price')
                    ->numeric()
                    ->prefix('$'),
                DateTimePicker::make('starts_at')
                    ->label('Starts at'),
                DateTimePicker::make('ends_at')
                    ->label('Ends at'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
