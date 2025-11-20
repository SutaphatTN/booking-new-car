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

    public function getFullAddressAttribute()
    {
        $parts = [
            $this->house_number ? 'บ้านเลขที่ ' . $this->house_number : null,
            $this->group ? 'หมู่ ' . $this->group : null,
            $this->village ? 'หมู่บ้าน ' . $this->village : null,
            $this->alley ? 'ซอย ' . $this->alley : null,
            $this->road ? 'ถนน ' . $this->road : null,
            $this->subdistrict ? 'ตำบล ' . $this->subdistrict : null,
            $this->district ? 'อำเภอ ' . $this->district : null,
            $this->province ? 'จังหวัด ' . $this->province : null,
            $this->postal_code ? $this->postal_code : null,
        ];

        return implode(' ', array_filter($parts));
    }
}
