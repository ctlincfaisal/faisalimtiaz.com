<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingFollowupEmailOpen extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_followup_email_id',
        'email',
        'tracking_id',
        'opened_at',
        'last_opened_at',
        'open_count',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'last_opened_at' => 'datetime',
    ];
}
