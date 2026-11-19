<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingBillingWebhookEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'paddle_event_id',
        'event_type',
        'occurred_at',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
        'payload' => 'array',
    ];
}
