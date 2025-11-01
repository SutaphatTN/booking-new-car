<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Address extends Model
{
    use SoftDeletes;

    protected $table = 'address';

    protected $fillable = [
        'customer_id',
        'type',
        'house_number',
        'group',
        'village',
        'alley',
        'road',
        'subdistrict',
        'district',
        'province',
        'postal_code',
        'userZone',
    ];

    protected $dates = ['deleted_at'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
