<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsiAssessment extends Model
{
    protected $table = 'ssi_assessments';

    protected $fillable = [
        'ssi_record_id',
        'dw_website',
        'q11_facilities',
        'q15_car_knowledge',
        'q17_service_responsibility',
        'q18_sales_conditions',
        'o27_car_condition',
        'fu_followup',
        'recommend_showroom',
        'sop14_test_drive',
        'sop24_update_progress',
        'sop25_accessories_complete',
        'sop30_satisfaction_followup',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
