<?php

namespace App\Models\Takers;

use App\Models\Deliveries\Delivery;
use Illuminate\Database\Eloquent\Model;
use Veelasky\LaravelHashId\Eloquent\HashableId;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegisterData extends Model
{
    use HasFactory;
    use HashableId;

    protected $guarded = [];

    protected $hidden = ['id', 'taker_id', 'group_id', 'delivery_id'];

    protected $appends = ['hash'];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function delivery(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function taker(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Taker::class, 'taker_id');
    }
}
