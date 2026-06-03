<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('video_path')
                    ->default(null),
                TextInput::make('video_width')
                    ->required()
                    ->numeric()
                    ->default(1920),
                TextInput::make('video_height')
                    ->required()
                    ->numeric()
                    ->default(1080),
                TextInput::make('duration_seconds')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
