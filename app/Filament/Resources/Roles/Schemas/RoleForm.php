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
                    ->label('Role name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    // The super_admin role cannot be renamed (exclusive to JEMO)
                    ->disabled(fn ($record): bool => $record?->name === 'super_admin')
                    ->dehydrated(fn ($record): bool => $record?->name !== 'super_admin'),

                Select::make('permissions')
                    ->label('Permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Assign permissions to this role. Fine-grained per-resource enforcement will be enabled progressively.'),
            ]);
    }
}
