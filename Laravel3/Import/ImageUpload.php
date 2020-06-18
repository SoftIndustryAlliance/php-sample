<?php

namespace App\Import;

use App\Models\File;

/**
 * Image uploader.
 *
 * @package App\Import
 */
class ImageUpload extends FileUpload
{

    public function upload(string $url, string $path = ''): File
    {
        return parent::upload($url, 'images');
    }
}
