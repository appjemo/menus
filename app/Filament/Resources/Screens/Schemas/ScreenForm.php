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
                    ->label('Nombre de la pantalla')
                    ->placeholder('Ej: Pantalla caja 1')
                    ->required()
                    ->maxLength(255),
                Select::make('template_id')
                    ->label('Plantilla')
                    // Solo plantillas de la compañía actual (evita fuga entre tenants)
                    ->options(fn () => Template::query()
                        ->where('company_id', Filament::getTenant()?->getKey())
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ]);
        // token y last_seen_at se gestionan automáticamente (no se editan a mano)
    }
}
