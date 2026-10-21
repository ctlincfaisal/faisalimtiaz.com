<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingEmailGroupContact extends Model
{
    public const SOURCES = [
        'old_client' => 'Old client',
        'new_lead' => 'New lead',
        'users' => 'Users',
    ];

    protected $fillable = ['name', 'project', 'email', 'source'];
}
