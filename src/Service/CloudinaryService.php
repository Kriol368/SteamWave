<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct(ParameterBagInterface $params)
    {
        // Retrieve Cloudinary credentials from environment variables
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $params->get('CLOUDINARY_CLOUD_NAME'),
                'api_key' => $params->get('CLOUDINARY_API_KEY'),
                'api_secret' => $params->get('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

    public function uploadFile($filePath, $folder = 'posts')
    {
        return $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
        ]);
    }
}
