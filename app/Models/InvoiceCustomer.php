<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class InvoiceCustomer extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'invoice_customer';

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'date',
        'license_plate',
        'engine_number',
        'vin_number',
        'code_number',
        'UserInsert',
        'request_date',
        'Approved',
        'UserApproved',
        'approved_date',
        'brand',
        'userZone',
        'branch',
    ];

    protected $dates = ['deleted_at'];

    public function accessories()
    {
        return $this->hasMany(InvoiceAccessory::class, 'inv_cust_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(TbBrand::class, 'brand', 'id');
    }

    public function getFormattedPhoneAttribute()
    {
        $mobile = $this->customer_phone;

        if (empty($mobile)) {
            return '-';
        }

        return substr($mobile, 0, 3) . '-' . substr($mobile, 3, 4) . '-' . substr($mobile, 7, 3);
    }

    public function insertInvoice()
    {
        return $this->belongsTo(User::class, 'UserInsert', 'id');
    }

    public function approvedInvoice()
    {
        return $this->belongsTo(User::class, 'UserApproved', 'id');
    }

    public function getFormattedDateAttribute()
    {
        return $this->date
            ? Carbon::parse($this->date)->locale('th')->translatedFormat('d M Y')
            : '-';
    }

    public function getFormattedRequestDateAttribute()
    {
        return $this->request_date
            ? Carbon::parse($this->request_date)->locale('th')->translatedFormat('d M Y')
            : '-';
    }

    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_date
            ? Carbon::parse($this->approved_date)->locale('th')->translatedFormat('d M Y')
            : '-';
    }
}
