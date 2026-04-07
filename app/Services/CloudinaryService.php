<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CloudinaryService
{
    /**
     * Upload an image to Cloudinary and return the secure URL.
     *
     * @param mixed $file
     * @param string $folder
     * @return string|null
     */
    public function upload($file, $folder = 'penerimaan_barang')
    {
        if (!$file) {
            return null;
        }

        $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
            'folder' => $folder,
            'transformation' => [
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
        ])->getSecurePath();

        return $uploadedFileUrl;
    }
}
