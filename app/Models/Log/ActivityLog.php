<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Model;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;
    use HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    protected $guarded = [];
    protected $hidden = ['id'];
    protected $appends = ['hash'];

    public function causer(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
