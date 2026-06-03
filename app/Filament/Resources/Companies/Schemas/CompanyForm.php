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
                Section::make('Restaurant details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Restaurant name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $record) {
                                if (! $record) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Identifier (URL)')
                            ->helperText('Used in the panel URL. Lowercase letters, numbers and dashes only.')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ]),

                // On create: the company's admin user is created
                Section::make('Initial admin user')
                    ->description('Created together with the company and assigned the "admin" role.')
                    ->visibleOn('create')
                    ->schema([
                        TextInput::make('admin_name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(false),
                        TextInput::make('admin_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->dehydrated(false),
                        TextInput::make('admin_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->dehydrated(false),
                    ]),

                // On edit: manage the company's users
                Select::make('users')
                    ->label('Company users')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visibleOn('edit')
                    ->helperText('Users who can administer this restaurant.'),
            ]);
    }
}
