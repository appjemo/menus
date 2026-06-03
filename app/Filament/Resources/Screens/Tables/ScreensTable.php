<?php

namespace App\Filament\Resources\Screens\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScreensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Screen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable(),
                TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->copyable()
                    ->copyMessage('Token copied')
                    ->searchable(),
                TextColumn::make('last_seen_at')
                    ->label('Last seen')
                    ->dateTime()
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->url(fn ($record) => url("/play/{$record->token}"))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
