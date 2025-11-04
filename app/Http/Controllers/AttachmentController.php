<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachments\Attachment;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    public function stream(Attachment $attachment, Filesystem $filesystem)
    {
        try {
            $filePath = storage_path('app/').$attachment->path;
            $fullPath = $attachment->path;

            // Check if file exists in storage
            if (!Storage::exists($fullPath)) {
                Log::warning('Attachment file not found', [
                    'attachment_id' => $attachment->id,
                    'file_path' => $fullPath,
                    'mime_type' => $attachment->mime,
                    'user_agent' => request()->userAgent(),
                    'ip' => request()->ip()
                ]);

                // Return placeholder image for images, error for other files
                if (str_starts_with($attachment->mime, 'image/')) {
                    return $this->getPlaceholderImage($attachment->mime);
                }

                return response()->json([
                    'error' => 'File not found',
                    'message' => 'The requested attachment is not available',
                    'attachment_id' => $attachment->id
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if file is readable
            if (!is_readable($filePath)) {
                Log::error('Attachment file not readable', [
                    'attachment_id' => $attachment->id,
                    'file_path' => $fullPath,
                    'file_exists' => file_exists($filePath),
                    'is_readable' => is_readable($filePath),
                    'permissions' => substr(sprintf('%o', fileperms($filePath)), -4)
                ]);

                if (str_starts_with($attachment->mime, 'image/')) {
                    return $this->getPlaceholderImage($attachment->mime);
                }

                return response()->json([
                    'error' => 'File not accessible',
                    'message' => 'The requested attachment is not accessible',
                    'attachment_id' => $attachment->id
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Get file content
            $file = $filesystem->get($filePath);

            // Validate file content
            if (empty($file)) {
                Log::error('Attachment file is empty', [
                    'attachment_id' => $attachment->id,
                    'file_path' => $fullPath,
                    'file_size' => Storage::size($fullPath)
                ]);

                if (str_starts_with($attachment->mime, 'image/')) {
                    return $this->getPlaceholderImage($attachment->mime);
                }

                return response()->json([
                    'error' => 'File is empty',
                    'message' => 'The requested attachment is empty',
                    'attachment_id' => $attachment->id
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $response = response()->make($file, 200);
            $response->header('Content-Type', $attachment->mime);

            // Add caching headers for better performance
            $response->header('Cache-Control', 'public, max-age=86400'); // Cache for 1 day
            $response->header('ETag', md5($file));

            return $response;

        } catch (\Exception $e) {
            Log::error('Error streaming attachment', [
                'attachment_id' => $attachment->id,
                'file_path' => $attachment->path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip()
            ]);

            if (str_starts_with($attachment->mime, 'image/')) {
                return $this->getPlaceholderImage($attachment->mime);
            }

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to stream attachment',
                'attachment_id' => $attachment->id
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate a placeholder image for missing images
     */
    private function getPlaceholderImage(string $originalMimeType): Response
    {
        // Create a simple SVG placeholder
        $svg = '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="#f3f4f6"/>
            <g transform="translate(200, 150)">
                <circle cx="0" cy="-20" r="30" fill="#ef4444" opacity="0.2"/>
                <path d="M -15,-25 L -5,-15 L 5,-25 L 15,-15 L 15,5 L -15,5 Z" fill="#ef4444" opacity="0.5"/>
                <circle cx="0" cy="0" r="2" fill="#ef4444"/>
                <text x="0" y="40" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="#6b7280">Image Not Available</text>
                <text x="0" y="60" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" fill="#9ca3af">The image could not be loaded</text>
            </g>
        </svg>';

        return response($svg, 404, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-cache'
        ]);
    }
}
