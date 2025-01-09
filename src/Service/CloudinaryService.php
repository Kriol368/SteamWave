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

    public function uploadPostFile($filePath, $folder = 'posts', $resourceType = 'auto')
    {
        return $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
            'resource_type' => $resourceType,
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

            // Log the extracted public ID
            error_log('Public ID: ' . $publicId);

            // Call Cloudinary's API to delete the asset using its public ID
            $response = $this->cloudinary->uploadApi()->destroy($publicId);

            // Log the Cloudinary response
            error_log('Cloudinary Response: ' . print_r($response, true));

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
        // Parse the URL to get the path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Remove the "upload/" part from the path (it comes right after "image/")
        $pathSegments = explode('/upload/', $path);

        // If there's no upload path segment, return an empty string
        if (count($pathSegments) < 2) {
            return '';
        }

        // The remaining part of the path after "upload/" will contain the version, folder, and public ID
        $pathAfterUpload = $pathSegments[1];

        // Remove the versioning part (e.g., v1734599118/) if present
        $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload); // Remove version (e.g., v1734599118/)

        // The path now includes the folder (if any) and the public ID, e.g., "pfp/ss9p6xgy956e4gflgx7o.jpg"
        // Split by '/' to isolate the folder and public ID
        $segments = explode('/', $pathAfterUpload);

        // Remove the file extension (e.g., .jpg) from the last segment (public ID)
        $publicIdWithExtension = array_pop($segments);
        $publicId = pathinfo($publicIdWithExtension, PATHINFO_FILENAME);

        // Rebuild the public ID by joining any folder structure with the public ID
        $publicId = implode('/', $segments) . '/' . $publicId;

        return $publicId;
    }









}
