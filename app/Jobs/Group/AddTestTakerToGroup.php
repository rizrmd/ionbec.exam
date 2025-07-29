<?php

namespace App\Jobs\Group;

use App\Models\Takers\Group;
use App\Models\Takers\Taker;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AddTestTakerToGroup implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private Taker $taker,
        private Group $group,
        private ?string $takerCode = null,
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::table('group_taker')
            ->updateOrInsert(
                ['group_id' => $this->group->id, 'taker_id' => $this->taker->id],
                ['taker_code' => $this->takerCode ?? str_pad($this->group->last_taker_code ?? 1, 3, '0', STR_PAD_LEFT)]
            );

        $this->group->last_taker_code = ($this->group->last_taker_code ?? 1) + 1;
        $this->group->save();
    }
}
