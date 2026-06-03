<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Restaurante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identificador')
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts('users')
                    ->badge(),
                TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products')
                    ->badge(),
                ToggleColumn::make('is_active')
                    ->label('Activa'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
