<?php

namespace App\Filament\Widgets;

use App\Models\Screen;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ScreensStatus extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Estado de pantallas';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Screen::query()
                    ->with(['template', 'company'])
                    ->when(
                        Filament::getTenant(),
                        fn (Builder $q, $tenant) => $q->where('company_id', $tenant->getKey()),
                    )
                    ->latest('last_seen_at')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Pantalla')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Compañía')
                    ->visible(fn () => Filament::getTenant() === null)
                    ->toggleable(),
                TextColumn::make('template.name')
                    ->label('Plantilla')
                    ->placeholder('Sin plantilla'),
                IconColumn::make('online')
                    ->label('Estado')
                    ->boolean()
                    ->state(fn (Screen $record): bool => $record->last_seen_at !== null
                        && $record->last_seen_at->gt(Carbon::now()->subMinutes(2))),
                TextColumn::make('last_seen_at')
                    ->label('Última vez vista')
                    ->since()
                    ->placeholder('Nunca'),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-play')
                    ->url(fn (Screen $record): string => url("/play/{$record->token}"))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Aún no hay pantallas')
            ->emptyStateDescription('Crea una pantalla y asígnale una plantilla para empezar.')
            ->paginated([10, 25]);
    }
}
