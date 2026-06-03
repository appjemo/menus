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
                    ->label('Pantalla')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Plantilla')
                    ->searchable(),
                TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->copyable()
                    ->copyMessage('Token copiado')
                    ->searchable(),
                TextColumn::make('last_seen_at')
                    ->label('Última vez vista')
                    ->dateTime()
                    ->since()
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
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
