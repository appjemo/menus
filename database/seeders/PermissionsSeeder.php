<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Crea los permisos por recurso y los asigna a los roles base.
     * Recursos controlados a nivel compañía: product, template, screen, promotion.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = ['product', 'template', 'screen', 'promotion'];
        $abilities = ['viewAny', 'create', 'update', 'delete'];

        $all = [];
        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                $name = "{$resource}.{$ability}";
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
                $all[] = $name;
            }
        }

        // Roles base
        foreach (['super_admin', 'admin', 'editor', 'viewer'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // super_admin y admin: control total de los recursos de su compañía
        Role::findByName('super_admin', 'web')->givePermissionTo($all);
        Role::findByName('admin', 'web')->givePermissionTo($all);

        // editor: edita productos y promociones; ve plantillas y pantallas
        Role::findByName('editor', 'web')->syncPermissions([
            'product.viewAny', 'product.create', 'product.update',
            'promotion.viewAny', 'promotion.create', 'promotion.update',
            'template.viewAny',
            'screen.viewAny',
        ]);

        // viewer: solo lectura
        Role::findByName('viewer', 'web')->syncPermissions([
            'product.viewAny', 'template.viewAny', 'screen.viewAny', 'promotion.viewAny',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Permisos creados y asignados a roles (admin/editor/viewer).');
    }
}
