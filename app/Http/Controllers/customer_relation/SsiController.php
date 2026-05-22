<?php

namespace App\Http\Controllers\customer_relation;

use App\Http\Controllers\Controller;
use App\Models\SsiRecord;
use App\Models\SsiContact;
use App\Models\SsiAssessment;
use App\Models\SsiPayment;
use App\Models\SsiFeedback;
use App\Models\SsiResolution;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SsiController extends Controller
{
    public function index()
    {
        return view('customer-relation.ssi.index');
    }

    public function list(Request $request)
    {
        $salecars = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
        ])
            ->whereNotNull('DeliveryDate')
            ->get();

        $no = 1;
        $data = $salecars->map(function ($s) use (&$no) {
            $c        = $s->customer;
            $fullName = $c
                ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';
            $phone    = $c->formatted_mobile ?? '-';
            $model = $s->model ? $s->model->Name_TH : '';
            $subModelSale = $s->subModel ? $s->subModel->name : '';
            $subDetail = $s->subModel ? $s->subModel->detail : '';

            $row = fn($icon, $class, $tip, $text) =>
                "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

            if ($s->brand == 2 || $s->brand == 3) {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale);
            } else {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale)
                     . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '');
            }

            return [
                'No'          => $no++,
                'salecar_id'  => $s->id,
                'FullName'    => $fullName,
                'Phone'       => $phone,
                'model'       => $car,
                'DeliveryDate' => $s->DeliveryDate
                    ? Carbon::parse($s->DeliveryDate)->format('d/m/Y')
                    : '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function edit($salecarId)
    {
        $salecar = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'carOrder',
        ])->findOrFail($salecarId);

        $ssiRecord = SsiRecord::with([
            'contacts',
            'assessment',
            'payment',
            'feedback',
            'resolution',
        ])->firstOrCreate(
            ['salecar_id' => $salecarId],
            [
                'userZone'   => Auth::user()->userZone,
                'brand'      => Auth::user()->brand,
                'branch'     => Auth::user()->branch,
                'UserInsert' => Auth::id(),
            ]
        );

        $c = $salecar->customer;

        $info = [
            'salecar_id'        => $salecar->id,
            'full_name'         => $c ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName) : '-',
            'phone'             => $c->formatted_mobile ?? '-',
            'model'             => $salecar->model?->Name_TH ?? '-',
            'sub_model'         => $salecar->subModel?->name ?? '-',
            'delivery_date'     => $salecar->DeliveryDate
                ? Carbon::parse($salecar->DeliveryDate)->format('d/m/Y')
                : '-',
            'delivery_location' => $salecar->delivery_location ?? '-',
            'delivery_province' => $salecar->delivery_province ?? '-',
            'vin_number'        => $salecar->carOrder?->vin_number ?? '-',
        ];

        return view('customer-relation.ssi.edit', compact('ssiRecord', 'info'));
    }

    public function saveContact(Request $request, $salecarId)
    {
        $request->validate([
            'contact_date'      => 'required|date',
            'contacted'         => 'required|boolean',
            'interview_success' => 'nullable|boolean',
            'remark'            => 'nullable|string|max:1000',
        ]);

        $ssiRecord = SsiRecord::firstOrCreate(
            ['salecar_id' => $salecarId],
            [
                'userZone'   => Auth::user()->userZone,
                'brand'      => Auth::user()->brand,
                'branch'     => Auth::user()->branch,
                'UserInsert' => Auth::id(),
            ]
        );

        $contact = SsiContact::create([
            'ssi_record_id'     => $ssiRecord->id,
            'contact_date'      => $request->contact_date,
            'contacted'         => $request->contacted,
            'interview_success' => $request->boolean('contacted') ? $request->interview_success : null,
            'remark'            => $request->remark,
        ]);

        $contactNumber = $ssiRecord->contacts()->count();

        return response()->json([
            'success'        => true,
            'contact'        => $this->formatContact($contact, $contactNumber),
            'contact_number' => $contactNumber,
        ]);
    }

    public function deleteContact($salecarId, $contactId)
    {
        $ssiRecord = SsiRecord::where('salecar_id', $salecarId)->firstOrFail();
        $contact   = SsiContact::where('ssi_record_id', $ssiRecord->id)
            ->where('id', $contactId)
            ->firstOrFail();
        $contact->delete();

        return response()->json(['success' => true]);
    }

    public function saveTab2(Request $request, $salecarId)
    {
        $ssiRecord = SsiRecord::firstOrCreate(
            ['salecar_id' => $salecarId],
            [
                'userZone'   => Auth::user()->userZone,
                'brand'      => Auth::user()->brand,
                'branch'     => Auth::user()->branch,
                'UserInsert' => Auth::id(),
            ]
        );

        // Card 1: Assessment
        SsiAssessment::updateOrCreate(
            ['ssi_record_id' => $ssiRecord->id],
            [
                'dw_website'                => $request->dw_website,
                'q11_facilities'            => $request->q11_facilities,
                'q15_car_knowledge'         => $request->q15_car_knowledge,
                'q17_service_responsibility' => $request->q17_service_responsibility,
                'q18_sales_conditions'      => $request->q18_sales_conditions,
                'o27_car_condition'         => $request->o27_car_condition,
                'fu_followup'               => $request->fu_followup,
                'recommend_showroom'        => $request->recommend_showroom,
                'sop14_test_drive'          => $request->sop14_test_drive,
                'sop24_update_progress'     => $request->sop24_update_progress,
                'sop25_accessories_complete' => $request->sop25_accessories_complete,
                'sop30_satisfaction_followup' => $request->sop30_satisfaction_followup,
            ]
        );

        // Card 2: Payment
        SsiPayment::updateOrCreate(
            ['ssi_record_id' => $ssiRecord->id],
            [
                'amount_admin'    => $request->amount_admin,
                'amount_customer' => $request->amount_customer,
                'payment_channel' => $request->payment_channel,
                'transfer_correct' => $request->transfer_correct,
                'remark'          => $request->payment_remark,
            ]
        );

        // Card 3: Feedback
        SsiFeedback::updateOrCreate(
            ['ssi_record_id' => $ssiRecord->id],
            [
                'compliment' => $request->compliment,
                'suggestion' => $request->suggestion,
                'complaint'  => $request->complaint,
            ]
        );

        // Card 4: Resolution
        SsiResolution::updateOrCreate(
            ['ssi_record_id' => $ssiRecord->id],
            [
                'cro_comment'               => $request->cro_comment,
                'sm_resolution'             => $request->sm_resolution,
                'resolution_date'           => $request->resolution_date ?: null,
                'resolution_status'         => $request->resolution_status,
                'correction_form_sent_date' => $request->correction_form_sent_date ?: null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
    }

    private function formatContact(SsiContact $contact, int $no): array
    {
        return [
            'id'                => $contact->id,
            'no'                => $no,
            'contact_date'      => $contact->contact_date
                ? Carbon::parse($contact->contact_date)->format('d/m/Y')
                : '-',
            'contacted'         => $contact->contacted,
            'interview_success' => $contact->interview_success,
            'remark'            => $contact->remark ?? '',
        ];
    }

    public function getContacts($salecarId)
    {
        $ssiRecord = SsiRecord::where('salecar_id', $salecarId)->first();
        if (!$ssiRecord) {
            return response()->json(['contacts' => []]);
        }

        $contacts = $ssiRecord->contacts()->get();
        $formatted = $contacts->values()->map(function ($c, $i) {
            return $this->formatContact($c, $i + 1);
        });

        return response()->json(['contacts' => $formatted]);
    }
}
