<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la plantilla')
                    ->placeholder('Ej: Menú Almuerzo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('video_path')
                    ->label('Ruta/URL del video (Google Cloud Storage)')
                    ->helperText('Más adelante esto será una subida directa a GCS.')
                    ->maxLength(255),
                TextInput::make('video_width')
                    ->label('Ancho del video (px)')
                    ->required()
                    ->numeric()
                    ->default(1920),
                TextInput::make('video_height')
                    ->label('Alto del video (px)')
                    ->required()
                    ->numeric()
                    ->default(1080),
                TextInput::make('duration_seconds')
                    ->label('Duración (segundos)')
                    ->numeric(),
            ]);
    }
}
