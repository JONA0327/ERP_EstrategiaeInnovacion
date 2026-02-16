<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MoveExpedientesToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rh:migrate-expedientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mueve los expedientes de storage/app/public a storage/app/private para seguridad.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migración de expedientes...');

        $publicDisk = Storage::disk('public');
        $localDisk = Storage::disk('local'); // Private

        // Ruta relativa en ambos discos
        $directory = 'expedientes';

        if (!$publicDisk->exists($directory)) {
            $this->warn('No se encontró la carpeta "expedientes" en el disco público. Nada que migrar.');
            return;
        }

        $allFiles = $publicDisk->allFiles($directory);
        $count = count($allFiles);
        $this->info("Se encontraron {$count} archivos para migrar.");

        $bar = $this->output->createProgressBar($count);
        $migrated = 0;
        $errors = 0;

        foreach ($allFiles as $file) {
            try {
                // Verificar si ya existe en privado
                if ($localDisk->exists($file)) {
                // Si ya existe, solo borramos el público para limpiar
                // $this->warn("El archivo {$file} ya existe en privado. Eliminando versión pública.");
                }
                else {
                    // Mover: Leer del público y escribir en privado
                    $content = $publicDisk->get($file);
                    $localDisk->put($file, $content);
                    $migrated++;
                }

                // Opción segura: Eliminar del público después de copiar
                $publicDisk->delete($file);

                $bar->advance();
            }
            catch (\Exception $e) {
                $this->error("Error migrando {$file}: " . $e->getMessage());
                $errors++;
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Migración completada. Migrados: {$migrated}. Errores: {$errors}.");

    // Intentar limpiar directorios vacíos en public
    // (Laravel no tiene un deleteDirectoryRecursiveEmpty nativo fácil en Storage, así que lo dejamos así o usamos File)
    }
}
