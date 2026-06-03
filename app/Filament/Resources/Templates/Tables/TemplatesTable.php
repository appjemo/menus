<?php

namespace App\Filament\Resources\Templates\Tables;

use App\Filament\Resources\Templates\TemplateResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Plantilla')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('video_path')
                    ->label('Video')
                    ->boolean()
                    ->state(fn ($record) => filled($record->video_path)),
                TextColumn::make('slots_count')
                    ->label('Precios')
                    ->counts('slots')
                    ->badge(),
                TextColumn::make('video_width')
                    ->label('Resolución')
                    ->formatStateUsing(fn ($record) => "{$record->video_width}×{$record->video_height}"),
            ])
            ->recordActions([
                Action::make('slots')
                    ->label('Editor visual')
                    ->icon('heroicon-o-cursor-arrow-rays')
                    ->color('primary')
                    ->url(fn ($record) => TemplateResource::getUrl('slots', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
