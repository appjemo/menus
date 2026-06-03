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
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identifier')
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge(),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->badge(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
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
