<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipients',
        'recipient_count',
        'sent_count',
        'failed_count',
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
        'delivery_status',
        'delivery_error',
        'sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function opens()
    {
        return $this->hasMany(MarketingEmailOpen::class);
    }

    public function followups()
    {
        return $this->hasMany(MarketingFollowupEmail::class);
    }
}
