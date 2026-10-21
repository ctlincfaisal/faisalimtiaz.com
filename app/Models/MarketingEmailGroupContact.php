<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingEmailGroupContact extends Model
{
    protected $fillable = ['name', 'project', 'email', 'source'];
}
