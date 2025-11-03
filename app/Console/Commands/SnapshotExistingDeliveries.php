<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deliveries\Delivery;
use App\Services\ExamSnapshotService;
use Illuminate\Support\Facades\DB;

class SnapshotExistingDeliveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:snapshot-existing
                            {--force : Force snapshot creation even if snapshot exists}
                            {--delivery= : Snapshot a specific delivery by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create snapshots for existing deliveries that don\'t have one yet';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $snapshotService = app(ExamSnapshotService::class);
        $force = $this->option('force');
        $deliveryId = $this->option('delivery');

        // Get delivery IDs first (avoid Carbon issues with full model loading)
        $query = DB::table('deliveries');

        if ($deliveryId) {
            $query->where('id', $deliveryId);
        } elseif (!$force) {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('delivery_snapshots')
                  ->whereColumn('delivery_snapshots.delivery_id', 'deliveries.id');
            });
        }

        $deliveryIds = $query->pluck('id');

        if ($deliveryIds->isEmpty()) {
            $this->info('No deliveries found that need snapshots.');
            return 0;
        }

        $this->info("Found {$deliveryIds->count()} deliveries to process.");
        $bar = $this->output->createProgressBar($deliveryIds->count());
        $bar->start();

        $created = 0;
        $updated = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($deliveryIds as $id) {
            $delivery = null;

            try {
                // Load delivery one by one to avoid Carbon bulk loading issues
                // Wrap in try-catch to skip deliveries with corrupt date data
                try {
                    $delivery = Delivery::withoutGlobalScope(\App\Scopes\DeliveryScope::class)
                        ->withoutGlobalScope(\App\Scopes\ClientScope::class)
                        ->with('exam', 'snapshot')
                        ->find($id);
                } catch (\TypeError $e) {
                    // Skip deliveries with corrupt date data
                    if (str_contains($e->getMessage(), 'Carbon') || str_contains($e->getMessage(), 'setLastErrors')) {
                        $skipped++;
                        $this->newLine();
                        $this->warn("  ⊗ Skipped Delivery ID {$id}: Corrupt date data");
                        $bar->advance();
                        continue;
                    }
                    throw $e;
                }

                if (!$delivery) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$delivery->exam) {
                    $skipped++;
                    $this->newLine();
                    $this->warn("  ⊗ Skipped Delivery ID {$id}: No exam found");
                    $bar->advance();
                    continue;
                }

                if ($delivery->snapshot && $force) {
                    // Refresh snapshot
                    $snapshotService->refreshSnapshot($delivery);
                    $updated++;
                    $this->newLine();
                    $this->line("  ✓ Updated snapshot for Delivery ID {$id}: {$delivery->name}");
                } elseif (!$delivery->snapshot) {
                    // Create new snapshot
                    $snapshotService->createSnapshot($delivery);
                    $created++;
                    $this->newLine();
                    $this->line("  ✓ Created snapshot for Delivery ID {$id}: {$delivery->name}");
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Delivery ID {$id}: " . $e->getMessage();
                $this->newLine();
                $this->error("  ✗ Failed for Delivery ID {$id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('=== Summary ===');
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Failed', $failed],
                ['Total Processed', $deliveryIds->count()],
            ]
        );

        if (!empty($errors)) {
            $this->newLine();
            $this->error('=== Errors ===');
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        return $failed > 0 ? 1 : 0;
    }
}
