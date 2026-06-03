<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    // Requerida al crear; al editar, en blanco = no cambiar
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->helperText('Al editar, deja en blanco para conservar la actual.')
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Roles')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        // Un admin de compañía no puede asignar super_admin
                        modifyQueryUsing: fn (Builder $query) => Auth::user()?->isSuperAdmin()
                            ? $query
                            : $query->where('name', '!=', 'super_admin'),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
