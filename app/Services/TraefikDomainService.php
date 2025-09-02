<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Yaml\Yaml;

class TraefikDomainService
{
    private const CONFIG_PATH = '/traefik-dynamic/mdxm.yaml';
    
    // Your Coolify docker-compose app service (consistent format across deployments)
    private const SERVICE_NAME = 'app-okksscs4w0s8oc0go0k4cg8k';
    private const SERVICE_URL = 'http://app-okksscs4w0s8oc0go0k4cg8k:3000';  // Internal Docker network URL
    
    public function updateMdxmConfig(): void
    {
        try {
            $activeClients = Client::where('is_active', true)
                ->whereNotNull('domains')
                ->get();
            
            $config = $this->generateTraefikConfig($activeClients);
            $this->writeConfig($config);
            
            Log::info('Traefik mdxm.yaml updated successfully', [
                'clients_count' => $activeClients->count(),
                'total_domains' => $activeClients->sum(fn($client) => count($client->domains ?? []))
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update Traefik mdxm.yaml', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    private function generateTraefikConfig($clients): array
    {
        $config = [
            'http' => [
                'routers' => [],
                'services' => [],
                'middlewares' => [
                    'https-redirect' => [
                        'redirectScheme' => [
                            'scheme' => 'https',
                            'permanent' => true
                        ]
                    ]
                ]
            ]
        ];
        
        foreach ($clients as $client) {
            if (empty($client->domains)) {
                continue;
            }
            
            foreach ($client->domains as $domain) {
                $domain = trim($domain);
                if (empty($domain)) {
                    continue;
                }
                
                $routerName = 'mdxm-' . $client->id . '-' . str_replace(['.', '/', ':'], '-', $domain);
                
                // HTTPS router
                $config['http']['routers'][$routerName . '-https'] = [
                    'rule' => "Host(`{$domain}`)",
                    'service' => self::SERVICE_NAME,
                    'tls' => [
                        'certResolver' => 'letsencrypt'
                    ],
                    'entryPoints' => ['https']
                ];
                
                // HTTP router with redirect to HTTPS
                $config['http']['routers'][$routerName . '-http'] = [
                    'rule' => "Host(`{$domain}`)",
                    'entryPoints' => ['http'],
                    'middlewares' => ['https-redirect'],
                    'service' => self::SERVICE_NAME
                ];
            }
        }
        
        return $config;
    }
    
    private function writeConfig(array $config): void
    {
        $yamlContent = Yaml::dump($config, 4, 2);
        
        // Atomic write to prevent corruption
        $tempFile = self::CONFIG_PATH . '.tmp';
        file_put_contents($tempFile, $yamlContent);
        rename($tempFile, self::CONFIG_PATH);
    }
    
    public function testWrite(): bool
    {
        try {
            $testContent = "# Test write at " . now()->toISOString() . "\n";
            file_put_contents(self::CONFIG_PATH . '.test', $testContent);
            unlink(self::CONFIG_PATH . '.test');
            return true;
        } catch (\Exception $e) {
            Log::error('Traefik config directory not writable', ['error' => $e->getMessage()]);
            return false;
        }
    }
}