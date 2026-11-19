<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingBillingSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_account_id',
        'paddle_subscription_id',
        'paddle_customer_id',
        'status',
        'price_id',
        'product_id',
        'started_at',
        'next_billed_at',
        'scheduled_change_action',
        'scheduled_change_effective_at',
        'paddle_created_at',
        'last_event_at',
        'items',
        'custom_data',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'next_billed_at' => 'datetime',
        'scheduled_change_effective_at' => 'datetime',
        'paddle_created_at' => 'datetime',
        'last_event_at' => 'datetime',
        'items' => 'array',
        'custom_data' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(MarketingAccount::class, 'marketing_account_id');
    }
}
