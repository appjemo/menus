<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del restaurante')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del restaurante')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $record) {
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
                    ]),

                // Al crear: se genera el usuario administrador de la compañía
                Section::make('Usuario administrador inicial')
                    ->description('Se crea junto con la compañía y se le asigna el rol "admin".')
                    ->visibleOn('create')
                    ->schema([
                        TextInput::make('admin_name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false),
                        TextInput::make('admin_email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->dehydrated(false),
                        TextInput::make('admin_password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->dehydrated(false),
                    ]),

                // Al editar: gestionar usuarios de la compañía
                Select::make('users')
                    ->label('Usuarios de esta compañía')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visibleOn('edit')
                    ->helperText('Usuarios que pueden administrar este restaurante.'),
            ]);
    }
}
