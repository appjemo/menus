<?php

namespace App\Filament\Resources\Templates\Schemas;

use Filament\Forms\Components\FileUpload;
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
                FileUpload::make('video_path')
                    ->label('Video de fondo (.mp4)')
                    ->helperText('Sube el .mp4 exportado de After Effects (sin precios). Se guarda en Google Cloud Storage.')
                    ->disk('gcs')
                    ->directory('videos')
                    ->visibility('public')
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(204800) // 200 MB
                    ->downloadable()
                    ->columnSpanFull(),
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
