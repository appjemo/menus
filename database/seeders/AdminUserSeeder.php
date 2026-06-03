<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea los roles base y el usuario super administrador (JEMO).
     * Credenciales desde el .env: ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     */
    public function run(): void
    {
        // Roles base del sistema
        foreach (['super_admin', 'admin', 'editor', 'viewer'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command->warn('AdminUserSeeder: define ADMIN_EMAIL y ADMIN_PASSWORD en el .env');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrador'),
                'password' => $password, // el cast 'hashed' del modelo lo encripta
            ],
        );

        $user->syncRoles(['super_admin']);

        $this->command->info("Super admin listo: {$user->email}");
    }
}
