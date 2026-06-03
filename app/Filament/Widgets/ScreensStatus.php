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

    protected static ?string $heading = 'Screen status';

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
                    ->label('Screen')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->visible(fn () => Filament::getTenant() === null)
                    ->toggleable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->placeholder('No template'),
                IconColumn::make('online')
                    ->label('Status')
                    ->boolean()
                    ->state(fn (Screen $record): bool => $record->last_seen_at !== null
                        && $record->last_seen_at->gt(Carbon::now()->subMinutes(2))),
                TextColumn::make('last_seen_at')
                    ->label('Last seen')
                    ->since()
                    ->placeholder('Never'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-play')
                    ->url(fn (Screen $record): string => url("/play/{$record->token}"))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No screens yet')
            ->emptyStateDescription('Create a screen and assign it a template to get started.')
            ->paginated([10, 25]);
    }
}
