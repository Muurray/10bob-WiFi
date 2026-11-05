<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotspotUser extends Model
{
    protected $fillable = [
        'payment_id',
        'username',
        'password',
        'phone',
        'status',
        'provisioned_at',
        'provisioning_error',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'provisioned_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
