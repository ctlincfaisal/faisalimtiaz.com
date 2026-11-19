<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class MarketingTemplate extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'subject',
        'subject_options',
        'content',
        'content_format',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'subject_options' => 'array',
    ];
}
