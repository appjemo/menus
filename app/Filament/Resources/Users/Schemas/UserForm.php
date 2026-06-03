<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    // Required on create; on edit, leave blank to keep current
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->helperText('When editing, leave blank to keep the current password.')
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Roles')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        // The super_admin role is exclusive to JEMO: never assignable here
                        modifyQueryUsing: fn (Builder $query) => $query->where('name', '!=', 'super_admin'),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
