<?php

namespace Steak\Concerns;

trait ModelFileMethods
{
    public function getSecretPath($path = null): string
    {
        return config('Steak.file_storage_path', 'storage/app') . ($path ? '/' . ltrim($path, '/') : '');
    }
}