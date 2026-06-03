<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del restaurante')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, ?string $old, $record) {
                        // Autogenera el slug desde el nombre solo al crear (no al editar)
                        if (! $record) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Identificador (URL)')
                    ->helperText('Se usa en la URL del panel. Solo minúsculas, números y guiones.')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true)
                    ->required(),
                Select::make('users')
                    ->label('Usuarios de esta compañía')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Usuarios que podrán administrar este restaurante.'),
            ]);
    }
}
