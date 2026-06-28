<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'subject_options',
        'content',
        'attachment_path',
        'attachment_name',
    ];

    protected $casts = [
        'subject_options' => 'array',
    ];
}
