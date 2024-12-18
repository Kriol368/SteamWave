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

    public function deletePfp(string $imageUrl): void
    {
        try {
            // Extract the public ID from the URL
            $publicId = $this->extractPublicIdFromUrl($imageUrl);

            // Call Cloudinary's API to delete the asset using its public ID
            $this->cloudinary->uploadApi()->destroy($publicId);
        } catch (\Exception $e) {
            // Handle error (log it, notify admin, etc.)
            throw new \Exception('Error deleting image from Cloudinary: ' . $e->getMessage());
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
    // Helper function to extract the public ID from the URL
    private function extractPublicIdFromUrl(string $url): string
    {
        // Cloudinary URLs have the following format:
        // https://res.cloudinary.com/{cloud_name}/image/upload/{folder}/{public_id}.{extension}
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Assuming the format is consistent with Cloudinary's URL structure
        $segments = explode('/', trim($path, '/'));

        // The public ID is usually in the last segment of the URL path, just before the extension
        $publicIdWithExtension = array_pop($segments);

        // Remove the extension part (e.g., .jpg or .png)
        $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);

        return $publicId;
    }






}
