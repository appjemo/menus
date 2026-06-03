<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Datos de demostración: una compañía con productos de ejemplo,
     * y el super admin asociado para poder verla en el panel.
     */
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Restaurante Demo', 'is_active' => true],
        );

        // Asociar el super admin (si existe) a la compañía demo
        if ($admin = User::where('email', env('ADMIN_EMAIL'))->first()) {
            $company->users()->syncWithoutDetaching([$admin->id]);
        }

        $productos = [
            ['name' => 'Hamburguesa Clásica', 'price' => 9.99, 'category' => 'Hamburguesas', 'sort_order' => 1],
            ['name' => 'Hamburguesa Doble', 'price' => 12.99, 'category' => 'Hamburguesas', 'sort_order' => 2],
            ['name' => 'Pizza Margarita', 'price' => 12.99, 'category' => 'Pizzas', 'sort_order' => 3],
            ['name' => 'Combo #1', 'price' => 15.50, 'category' => 'Combos', 'sort_order' => 4],
            ['name' => 'Soda', 'price' => 1.99, 'category' => 'Bebidas', 'sort_order' => 5],
        ];

        foreach ($productos as $p) {
            Product::updateOrCreate(
                ['company_id' => $company->id, 'name' => $p['name']],
                $p + ['company_id' => $company->id, 'is_active' => true],
            );
        }

        $this->command->info("Compañía demo lista: {$company->name} ({$company->products()->count()} productos)");
    }
}
