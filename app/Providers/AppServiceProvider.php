<?php

namespace App\Providers;

use App\Filesystem\PublicGcsAdapter;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El super admin (JEMO) tiene acceso total (bypassa todas las policies)
        Gate::before(fn ($user, $ability) => $user->hasRole('super_admin') ? true : null);

        // Permitir subidas temporales grandes en Livewire (videos hasta 200 MB)
        config(['livewire.temporary_file_upload.rules' => ['file', 'max:204800']]);

        // Disco 'gcs' para Google Cloud Storage (videos de plantillas)
        Storage::extend('gcs', function ($app, array $config) {
            $client = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file_path'],
            ]);

            $bucket = $client->bucket($config['bucket']);
            // PublicGcsAdapter: uniform bucket-level access (sin ACL por objeto) +
            // getUrl() para que Laravel 13 genere URLs públicas.
            $adapter = new PublicGcsAdapter(
                $bucket,
                $config['path_prefix'] ?? '',
                $config['url'],
            );

            return new FilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }
}
