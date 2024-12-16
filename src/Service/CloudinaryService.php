<?php

namespace App\Service;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct(string $cloudinaryUrl, string $apiKey, string $apiSecret)
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudinaryUrl,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }

    public function uploadPostFile($filePath, $folder = 'posts')
    {
        return $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
        ]);
    }


    // Method to retrieve the URL of an uploaded post file
    public function getPostFileUrl(string $publicId)
    {
        // You can use Cloudinary API to generate a URL for the uploaded file (image/video)
        try {
            $url = $this->cloudinary->image($publicId)->getPublicId();
        } catch (\Exception $e) {
            $url = null; // If something goes wrong, return null
        }

        return $url;
    }

    public function uploadPfp(string $fileUrl): ?string
    {
        try {
            $uploadResponse = $this->cloudinary->uploadApi()->upload($fileUrl, [
                'folder' => 'pfp'
            ]);

            return $uploadResponse['secure_url'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Method to retrieve the URL of an uploaded profile picture (PFP)
    public function getProfilePictureUrl(string $publicId)
    {
        // You can use Cloudinary API to generate a URL for the uploaded profile picture
        try {
            $url = $this->cloudinary->image($publicId)->getPublicId();
        } catch (\Exception $e) {
            $url = null; // If something goes wrong, return null
        }

        return $url;
    }



}