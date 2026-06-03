<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Asocia el usuario recién creado a la compañía (tenant) actual.
     */
    protected function afterCreate(): void
    {
        if ($tenant = Filament::getTenant()) {
            $this->record->companies()->syncWithoutDetaching([$tenant->getKey()]);
        }
    }
}
