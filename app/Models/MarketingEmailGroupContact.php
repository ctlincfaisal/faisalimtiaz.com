<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class MarketingEmailGroupContact extends Model
{
    use MarketingAccountScoped;
    public const SOURCES = [
        'old_client' => 'Old client',
        'new_lead' => 'New lead',
        'users' => 'Users',
    ];

    protected $fillable = ['marketing_account_id', 'name', 'project', 'email', 'source'];
}
