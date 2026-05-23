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
        // GWM (brand 2) fields
        'gwm_q1', 'gwm_q1_reasons', 'gwm_q1_other',
        'gwm_q2', 'gwm_q2_reasons', 'gwm_q2_other',
        'gwm_q3', 'gwm_q3_reasons', 'gwm_q3_other',
        'gwm_q4', 'gwm_q4_reasons', 'gwm_q4_other',
        'gwm_q5', 'gwm_q5_reasons', 'gwm_q5_other',
        'gwm_q6', 'gwm_q6_reasons', 'gwm_q6_other',
        'gwm_q7',
        'gwm_q8',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
