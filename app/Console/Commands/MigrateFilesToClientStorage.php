<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Attachments\Attachment;
use App\Services\ClientStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MigrateFilesToClientStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:client-files 
                            {--client= : Specific client slug to migrate}
                            {--dry-run : Show what would be migrated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from legacy storage to client-scoped storage structure';

    protected $migrationStats = [
        'files_migrated' => 0,
        'errors' => 0,
        'clients_processed' => 0,
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificClient = $this->option('client');

        $this->info('🚀 Starting File Migration to Client-Scoped Storage');
        $this->info($isDryRun ? '📋 DRY RUN MODE - No changes will be made' : '⚠️  LIVE MODE - Files will be migrated');

        try {
            // Get clients to process
            $clients = $specificClient 
                ? Client::where('slug', $specificClient)->get()
                : Client::all();

            if ($clients->isEmpty()) {
                $this->error('No clients found to process');
                return 1;
            }

            foreach ($clients as $client) {
                $this->migrateClientFiles($client, $isDryRun);
            }

            $this->displayStatistics();
            
        } catch (\Exception $e) {
            $this->error("❌ Migration failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function migrateClientFiles(Client $client, bool $isDryRun)
    {
        $this->info("\n📂 Processing client: {$client->name} ({$client->slug})");

        if (!$isDryRun) {
            // Create client directory structure
            ClientStorageService::createClientDirectories($client);
            $this->info("✅ Created directory structure for client");
        }

        // Migrate attachments
        $this->migrateAttachments($client, $isDryRun);
        
        // Migrate user avatars/profile photos
        $this->migrateUserFiles($client, $isDryRun);
        
        // Migrate any other client-specific files
        $this->migrateClientAssets($client, $isDryRun);

        $this->migrationStats['clients_processed']++;
    }

    protected function migrateAttachments(Client $client, bool $isDryRun)
    {
        $attachments = Attachment::where('client_id', $client->id)->get();
        
        $this->info("Found {$attachments->count()} attachments for {$client->name}");

        foreach ($attachments as $attachment) {
            try {
                if (!Storage::disk('local')->exists($attachment->path)) {
                    $this->warn("⚠️  File not found: {$attachment->path}");
                    continue;
                }

                $oldPath = $attachment->path;
                $newPath = "attachments/" . basename($oldPath);

                if ($isDryRun) {
                    $this->info("Would migrate: {$oldPath} → clients/{$client->slug}/{$newPath}");
                } else {
                    // Get file content from old location
                    $content = Storage::disk('local')->get($oldPath);
                    
                    // Store in client-scoped location
                    ClientStorageService::storeContent($client, $newPath, $content, false);
                    
                    // Update attachment record
                    $attachment->update(['path' => $newPath]);
                    
                    $this->info("✅ Migrated: {$oldPath} → {$newPath}");
                }

                $this->migrationStats['files_migrated']++;

            } catch (\Exception $e) {
                $this->error("❌ Failed to migrate {$attachment->path}: " . $e->getMessage());
                $this->migrationStats['errors']++;
            }
        }
    }

    protected function migrateUserFiles(Client $client, bool $isDryRun)
    {
        // Migrate user profile photos/avatars
        $users = $client->users()->whereNotNull('profile_photo_path')->get();
        
        if ($users->isNotEmpty()) {
            $this->info("Found {$users->count()} user profile photos for {$client->name}");

            foreach ($users as $user) {
                try {
                    $oldPath = $user->profile_photo_path;
                    
                    // Skip if it's already a URL or doesn't exist
                    if (filter_var($oldPath, FILTER_VALIDATE_URL) || !Storage::disk('public')->exists($oldPath)) {
                        continue;
                    }

                    $newPath = "avatars/" . basename($oldPath);

                    if ($isDryRun) {
                        $this->info("Would migrate avatar: {$oldPath} → clients/{$client->slug}/{$newPath}");
                    } else {
                        // Get file content
                        $content = Storage::disk('public')->get($oldPath);
                        
                        // Store in client-scoped public location
                        ClientStorageService::storeContent($client, $newPath, $content, true);
                        
                        // Update user record
                        $user->update(['profile_photo_path' => $newPath]);
                        
                        $this->info("✅ Migrated avatar: {$oldPath} → {$newPath}");
                    }

                    $this->migrationStats['files_migrated']++;

                } catch (\Exception $e) {
                    $this->error("❌ Failed to migrate avatar {$user->profile_photo_path}: " . $e->getMessage());
                    $this->migrationStats['errors']++;
                }
            }
        }
    }

    protected function migrateClientAssets(Client $client, bool $isDryRun)
    {
        // Migrate client logos if they exist
        if ($client->logo && !filter_var($client->logo, FILTER_VALIDATE_URL)) {
            try {
                $oldPath = $client->logo;
                
                if (Storage::disk('public')->exists($oldPath)) {
                    $newPath = "logos/" . basename($oldPath);

                    if ($isDryRun) {
                        $this->info("Would migrate logo: {$oldPath} → clients/{$client->slug}/{$newPath}");
                    } else {
                        $content = Storage::disk('public')->get($oldPath);
                        ClientStorageService::storeContent($client, $newPath, $content, true);
                        
                        $client->update(['logo' => $newPath]);
                        
                        $this->info("✅ Migrated logo: {$oldPath} → {$newPath}");
                    }

                    $this->migrationStats['files_migrated']++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to migrate logo: " . $e->getMessage());
                $this->migrationStats['errors']++;
            }
        }
    }

    protected function displayStatistics()
    {
        $this->info("\n📊 Migration Statistics:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Clients Processed', $this->migrationStats['clients_processed']],
                ['Files Migrated', $this->migrationStats['files_migrated']],
                ['Errors', $this->migrationStats['errors']],
            ]
        );
    }
}
