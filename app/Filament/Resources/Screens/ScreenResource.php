<?php

namespace App\Filament\Resources\Screens;

use App\Filament\Resources\Screens\Pages\CreateScreen;
use App\Filament\Resources\Screens\Pages\EditScreen;
use App\Filament\Resources\Screens\Pages\ListScreens;
use App\Filament\Resources\Screens\Schemas\ScreenForm;
use App\Filament\Resources\Screens\Tables\ScreensTable;
use App\Models\Screen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScreenResource extends Resource
{
    protected static ?string $model = Screen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ScreenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScreensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScreens::route('/'),
            'create' => CreateScreen::route('/create'),
            'edit' => EditScreen::route('/{record}/edit'),
        ];
    }
}
