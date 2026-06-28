<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_visit_id',
        'session_id',
        'ip_address',
        'url',
        'path',
        'element',
        'element_text',
        'x',
        'y',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(WebsiteVisit::class, 'website_visit_id');
    }
}
