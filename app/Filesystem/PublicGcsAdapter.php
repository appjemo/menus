<?php

namespace App\Filesystem;

use Google\Cloud\Storage\Bucket;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility;

/**
 * Adaptador GCS que expone getUrl() para que Laravel 13 pueda generar URLs
 * públicas (FilesystemAdapter::url() solo usa el getUrl() del adapter).
 */
class PublicGcsAdapter extends GoogleCloudStorageAdapter
{
    public function __construct(
        Bucket $bucket,
        string $prefix,
        private string $publicBaseUrl,
    ) {
        parent::__construct($bucket, $prefix, new UniformBucketLevelAccessVisibility());
    }

    public function getUrl(string $path): string
    {
        return rtrim($this->publicBaseUrl, '/').'/'.ltrim($path, '/');
    }
}
