<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\Screen;
use App\Models\Slot;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Datos de demostración: compañía + productos + plantilla con slots + pantalla.
     * La pantalla demo queda accesible en /play/demo-pantalla-1
     */
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Restaurante Demo', 'is_active' => true],
        );

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

        // Plantilla demo (sin video aún → el Player muestra fondo de respaldo)
        $template = Template::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Menú Demo'],
            ['company_id' => $company->id, 'video_width' => 1920, 'video_height' => 1080],
        );

        // Slots: un renglón por producto (nombre + precio) en columna
        $template->slots()->delete();
        $y = 220;
        foreach ($company->products()->orderBy('sort_order')->get() as $product) {
            Slot::create([
                'template_id' => $template->id,
                'product_id' => $product->id,
                'pos_x' => 240,
                'pos_y' => $y,
                'font_size' => 64,
                'font_color' => '#FFFFFF',
                'align' => 'left',
                'show_name' => true,
            ]);
            $y += 140;
        }

        // Pantalla demo con token fijo y predecible para pruebas
        Screen::updateOrCreate(
            ['token' => 'demo-pantalla-1'],
            [
                'company_id' => $company->id,
                'template_id' => $template->id,
                'name' => 'Pantalla Demo',
            ],
        );

        $this->command->info("Demo lista: {$company->products()->count()} productos, plantilla con {$template->slots()->count()} slots, pantalla token=demo-pantalla-1");
    }
}
