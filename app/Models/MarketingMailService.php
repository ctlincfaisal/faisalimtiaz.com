<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use App\Support\MarketingAccountScoped;

class MarketingMailService extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'from_name',
        'from_email',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'is_active',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected $casts = [
        'smtp_port' => 'integer',
        'is_active' => 'boolean',
    ];

    public function setSmtpPassword(string $password): void
    {
        $this->smtp_password = Crypt::encryptString($password);
    }

    public function decryptedSmtpPassword(): string
    {
        return Crypt::decryptString($this->smtp_password);
    }
}
