<?php

namespace App\Jobs\Takers;

use App\Models\Takers\Taker;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Models\Takers\RegisterData;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Verification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?RegisterData $registrationData;
    protected Taker $taker;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?RegisterData $registrationData = null, ?Taker $taker = null)
    {
        $this->taker = $taker
            ?? $registrationData?->taker
            ?? throw new BadRequestException('At least registration data or taker required.');

        $this->registrationData = $registrationData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->taker->is_verified = true;
        $this->taker->save();

        if (is_null($this->registrationData)) {
            return;
        }

        $this->registrationData->load('group');

        // attach to a group.
        DB::table('group_taker')
            ->updateOrInsert(
                ['group_id' => $this->registrationData->group_id, 'taker_id' => $this->taker->id],
                ['taker_code' => str_pad($this->registrationData->group->last_taker_code, 3, '0', STR_PAD_LEFT)]
            );
    }
}
