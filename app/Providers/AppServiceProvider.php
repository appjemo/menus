<?php

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility;

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
        // Permitir subidas temporales grandes en Livewire (videos hasta 200 MB)
        config(['livewire.temporary_file_upload.rules' => ['file', 'max:204800']]);

        // Disco 'gcs' para Google Cloud Storage (videos de plantillas)
        Storage::extend('gcs', function ($app, array $config) {
            $client = new StorageClient([
                'projectId' => $config['project_id'],
                'keyFilePath' => $config['key_file_path'],
            ]);

            $bucket = $client->bucket($config['bucket']);
            // El bucket usa uniform bucket-level access: no se setean ACL por objeto
            // (la visibilidad pública se controla por IAM del bucket).
            $adapter = new GoogleCloudStorageAdapter(
                $bucket,
                $config['path_prefix'] ?? '',
                new UniformBucketLevelAccessVisibility(),
            );

            return new FilesystemAdapter(new Filesystem($adapter), $adapter, $config);
        });
    }
}
