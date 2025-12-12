<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Usuarios y Roles (Base)
            AdminUserSeeder::class,
            ProductionAdminSeeder::class,
            UserSeeder::class,

            // 2. Recursos Humanos (Dependen de Users)
            EmpleadoSeeder::class,
            RhUserSeeder::class,

            // 3. Catálogos y Configuración
            HelpSectionSeeder::class,
            LogisticaCorreosCCSeeder::class, // Ojo: tienes dos archivos similares, usa el correcto
            PostOperacionSeeder::class,
            
            // 4. Inventarios
            // InventarioSeeder::class, // Parece ser el viejo, revisa si debes usar este o el de abajo
            InventoryItemSeeder::class,

            // 5. Operaciones (Depende de todo lo anterior)
            OperacionLogisticaSeeder::class,
        ]);
    }
}