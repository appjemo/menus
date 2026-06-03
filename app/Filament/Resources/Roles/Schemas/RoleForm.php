<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('guard_name')->default('web'),

                TextInput::make('name')
                    ->label('Nombre del rol')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    // El rol super_admin no se puede renombrar (es exclusivo de JEMO)
                    ->disabled(fn ($record): bool => $record?->name === 'super_admin')
                    ->dehydrated(fn ($record): bool => $record?->name !== 'super_admin'),

                Select::make('permissions')
                    ->label('Permisos')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Asigna permisos a este rol. La aplicación fina de permisos por recurso se irá habilitando.'),
            ]);
    }
}
