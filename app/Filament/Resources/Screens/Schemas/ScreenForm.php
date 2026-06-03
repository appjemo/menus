<?php

namespace App\Filament\Resources\Screens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScreenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('template_id')
                    ->relationship('template', 'name')
                    ->default(null),
                TextInput::make('name')
                    ->required(),
                TextInput::make('token')
                    ->required(),
                DateTimePicker::make('last_seen_at'),
            ]);
    }
}
