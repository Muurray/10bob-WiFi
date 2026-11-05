<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'customer_id',
        'package_id',
        'amount',
        'phone',
        'email',
        'mpesa_receipt',
        'status',
        'reference',
        'access_expires_at',
        'payment_channel',
        'device_ip',
        'device_mac',
        'router_login_url',
        'router_redirect_url',
    ];

    protected function casts(): array
    {
        return [
            'access_expires_at' => 'datetime',
        ];
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function hotspotUser()
    {
        return $this->hasOne(HotspotUser::class);
    }
}
