<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class ClientStorageService
{
    /**
     * Get the storage disk for a specific client
     */
    public static function getDisk(Client $client): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $clientSlug = $client->slug;
        
        // Create dynamic disk configuration for this client
        config([
            "filesystems.disks.client_{$clientSlug}" => [
                'driver' => 'local',
                'root' => storage_path("app/clients/{$clientSlug}"),
                'url' => env('APP_URL') . "/storage/clients/{$clientSlug}",
                'visibility' => 'public',
            ]
        ]);
        
        return Storage::disk("client_{$clientSlug}");
    }

    /**
     * Get the public storage disk for a specific client
     */
    public static function getPublicDisk(Client $client): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $clientSlug = $client->slug;
        
        // Create dynamic public disk configuration for this client
        config([
            "filesystems.disks.client_{$clientSlug}_public" => [
                'driver' => 'local',
                'root' => storage_path("app/public/clients/{$clientSlug}"),
                'url' => env('APP_URL') . "/storage/clients/{$clientSlug}",
                'visibility' => 'public',
            ]
        ]);
        
        return Storage::disk("client_{$clientSlug}_public");
    }

    /**
     * Store a file for a specific client
     */
    public static function storeFile(
        Client $client, 
        UploadedFile $file, 
        string $directory = 'attachments',
        bool $public = false
    ): string {
        $disk = $public ? self::getPublicDisk($client) : self::getDisk($client);
        
        // Ensure directory exists
        $disk->makeDirectory($directory);
        
        return $file->store($directory, $disk);
    }

    /**
     * Store file content for a specific client
     */
    public static function storeContent(
        Client $client,
        string $path,
        string $content,
        bool $public = false
    ): bool {
        $disk = $public ? self::getPublicDisk($client) : self::getDisk($client);
        
        // Ensure directory exists
        $directory = dirname($path);
        if ($directory !== '.') {
            $disk->makeDirectory($directory);
        }
        
        return $disk->put($path, $content);
    }

    /**
     * Get file URL for a specific client
     */
    public static function getUrl(Client $client, string $path, bool $public = false): string
    {
        $disk = $public ? self::getPublicDisk($client) : self::getDisk($client);
        
        if ($public) {
            return $disk->url($path);
        }
        
        // For private files, you might want to create a route that serves them with authentication
        return route('client.file', ['client' => $client->slug, 'path' => $path]);
    }

    /**
     * Copy files from legacy storage to client-scoped storage
     */
    public static function migrateFiles(Client $client, array $filePaths = []): array
    {
        $migrated = [];
        $errors = [];
        
        foreach ($filePaths as $legacyPath) {
            try {
                // Determine if this is a public or private file
                $isPublic = str_starts_with($legacyPath, 'public/');
                $cleanPath = $isPublic ? str_replace('public/', '', $legacyPath) : $legacyPath;
                
                // Get source content
                $sourceContent = Storage::disk('local')->get($legacyPath);
                
                // Store in client-scoped location
                $newPath = self::storeContent($client, $cleanPath, $sourceContent, $isPublic);
                
                $migrated[$legacyPath] = $newPath;
                
            } catch (\Exception $e) {
                $errors[$legacyPath] = $e->getMessage();
            }
        }
        
        return [
            'migrated' => $migrated,
            'errors' => $errors
        ];
    }

    /**
     * Get the storage path for a client
     */
    public static function getClientStoragePath(Client $client, bool $public = false): string
    {
        $basePath = $public ? 'app/public' : 'app';
        return storage_path("{$basePath}/clients/{$client->slug}");
    }

    /**
     * Create client storage directories
     */
    public static function createClientDirectories(Client $client): void
    {
        $basePath = self::getClientStoragePath($client, false);
        $publicPath = self::getClientStoragePath($client, true);
        
        $directories = [
            $basePath . '/attachments',
            $basePath . '/exports',
            $basePath . '/temp',
            $publicPath . '/avatars',
            $publicPath . '/logos',
            $publicPath . '/assets',
        ];
        
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    /**
     * Delete all files for a client (when client is deleted)
     */
    public static function deleteClientFiles(Client $client): bool
    {
        try {
            $privatePath = self::getClientStoragePath($client, false);
            $publicPath = self::getClientStoragePath($client, true);
            
            if (File::exists($privatePath)) {
                File::deleteDirectory($privatePath);
            }
            
            if (File::exists($publicPath)) {
                File::deleteDirectory($publicPath);
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}