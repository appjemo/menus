<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Screen;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $online = fn ($query) => $query->where('last_seen_at', '>=', Carbon::now()->subMinutes(2));

        $stats = [];

        if ($tenant = Filament::getTenant()) {
            $totalScreens = $tenant->screens()->count();
            $onlineScreens = $online($tenant->screens())->count();

            $stats[] = Stat::make('Productos', $tenant->products()->count())
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary');

            $stats[] = Stat::make('Pantallas', $totalScreens)
                ->description("{$onlineScreens} en línea")
                ->descriptionIcon($onlineScreens > 0 ? 'heroicon-m-signal' : 'heroicon-m-signal-slash')
                ->color($onlineScreens > 0 ? 'success' : 'gray');

            $stats[] = Stat::make('Plantillas', $tenant->templates()->count())
                ->descriptionIcon('heroicon-m-film')
                ->color('primary');

            $stats[] = Stat::make('Promociones activas', $tenant->promotions()->where('is_active', true)->count())
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('warning');
        }

        // Vista global solo para super admin (JEMO)
        if (Auth::user()?->isSuperAdmin()) {
            $stats[] = Stat::make('Compañías (global)', Company::count())
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info');

            $stats[] = Stat::make('Pantallas totales (global)', Screen::count())
                ->description($online(Screen::query())->count().' en línea ahora')
                ->descriptionIcon('heroicon-m-tv')
                ->color('info');
        }

        return $stats;
    }
}
