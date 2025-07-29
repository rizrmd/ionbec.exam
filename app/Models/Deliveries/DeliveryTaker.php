<?php

namespace App\Models\Deliveries;

use App\Models\Takers\Taker;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryTaker extends Pivot
{
    use HasFactory;

    protected $table = 'delivery_taker';

    protected $fillable = [
        'delivery_id',
        'taker_id',
        'token',
        'is_login',
    ];

    protected $casts = [
        'is_login' => 'boolean',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    public function taker()
    {
        return $this->belongsTo(Taker::class, 'taker_id', 'id');
    }
}
