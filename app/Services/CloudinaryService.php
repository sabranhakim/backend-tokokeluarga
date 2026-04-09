<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $url = config('services.cloudinary.url');

        if (!$url) {
            throw new \Exception('Cloudinary configuration error: services.cloudinary.url is not set. Check your .env and config/services.php');
        }

        // Initialize directly with the URL string
        $this->cloudinary = new Cloudinary($url);
    }

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

        try {
            $response = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]);

            return $response['secure_url'];
        } catch (\Exception $e) {
            \Log::error('Cloudinary Upload Error: ' . $e->getMessage());
            return null;
        }
    }
}
