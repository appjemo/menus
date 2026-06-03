<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Crea el usuario administrador inicial de la compañía y lo asocia.
     */
    protected function afterCreate(): void
    {
        $data = $this->data;

        if (empty($data['admin_email'])) {
            return;
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => $data['admin_password'], // el cast 'hashed' lo encripta
        ]);

        $user->assignRole('admin');

        $this->record->users()->syncWithoutDetaching([$user->id]);
    }
}
