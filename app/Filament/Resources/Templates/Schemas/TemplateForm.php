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
                    ->label('Template name')
                    ->placeholder('e.g. Lunch Menu')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('video_path')
                    ->label('Background video (.mp4)')
                    ->helperText('Upload the .mp4 exported from After Effects (without prices). Stored in Google Cloud Storage.')
                    ->disk('gcs')
                    ->directory('videos')
                    ->visibility('public')
                    ->acceptedFileTypes(['video/mp4'])
                    ->maxSize(204800) // 200 MB
                    ->downloadable()
                    ->columnSpanFull(),
                TextInput::make('video_width')
                    ->label('Video width (px)')
                    ->required()
                    ->numeric()
                    ->default(1920),
                TextInput::make('video_height')
                    ->label('Video height (px)')
                    ->required()
                    ->numeric()
                    ->default(1080),
                TextInput::make('duration_seconds')
                    ->label('Duration (seconds)')
                    ->numeric(),
            ]);
    }
}
