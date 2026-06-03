<?php

use Illuminate\Support\Facades\Storage;

echo 'CONFIG: '.json_encode(config('filesystems.disks.gcs')).PHP_EOL;
Storage::disk('gcs')->put('test/hello.txt', 'hola gcs');
echo 'PUT_OK'.PHP_EOL;
echo 'EXISTS: '.(Storage::disk('gcs')->exists('test/hello.txt') ? 'si' : 'no').PHP_EOL;
