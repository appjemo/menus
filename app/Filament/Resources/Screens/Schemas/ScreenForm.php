<?php

namespace App\Filament\Resources\Screens\Schemas;

use App\Models\Template;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScreenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Screen name')
                    ->placeholder('e.g. Register screen 1')
                    ->required()
                    ->maxLength(255),
                Select::make('template_id')
                    ->label('Template')
                    // Only templates from the current company (prevents cross-tenant leak)
                    ->options(fn () => Template::query()
                        ->where('company_id', Filament::getTenant()?->getKey())
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ]);
        // token and last_seen_at are managed automatically (not edited by hand)
    }
}
