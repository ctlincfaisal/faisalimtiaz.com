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
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
        'sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'sent_at' => 'datetime',
    ];
}
