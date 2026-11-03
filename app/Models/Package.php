<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [

        'name',

        'price',

        'duration',

        'unit',

        'active'

    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}