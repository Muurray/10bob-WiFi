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
}
