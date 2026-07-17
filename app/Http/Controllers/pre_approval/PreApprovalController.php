<?php

namespace App\Http\Controllers\pre_approval;

use App\Http\Controllers\Controller;
use App\Models\Salecar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * โมดูล "ขออนุมัติเกินงบล่วงหน้า"
 *  - เก็บใน salecars (is_pre_approval = 1) แต่ถูก global scope 'preApproval' ซ่อนจากระบบจอง
 *  - รับเฉพาะเคส b1_md (ทะลุเพดาน) และ b2_gm (brand 2 เกินงบ)
 *  - อนุมัติแล้วต้องมีคน "กดสร้างการจอง" ถึงจะเข้าระบบจอง (บางคันอนุมัติแล้วลูกค้าไม่จอง)
 */
class PreApprovalController extends Controller
{
    /** role ที่กด "สร้างการจอง" ได้ */
    private const CONVERT_ROLES = ['admin', 'audit_lead', 'audit_dp', 'manager', 'gm'];

    /**
     * ลบคำขอได้ไหม
     *  - CONVERT_ROLES: ลบได้ทุกคำขอ (ที่ยังไม่ถูกสร้างเป็นการจอง)
     *  - sale/lead_sale: ลบได้เฉพาะ "ของตัวเอง" และ "ยังไม่ได้ส่งขออนุมัติ" (กันขยะค้าง)
     */
    private function canDelete(Salecar $s): bool
    {
        $user = Auth::user();

        if (!$s->is_pre_approval) {
            return false; // สร้างเป็นการจองแล้ว — ลบจากหน้านี้ไม่ได้
        }

        if (in_array($user->role, self::CONVERT_ROLES, true)) {
            return true;
        }

        return in_array($user->role, ['sale', 'lead_sale'], true)
            && (int) $s->SaleID === (int) $user->id
            && $s->approval_requested_at === null;
    }

    public function index()
    {
        return view('pre-approval.view');
    }

    /** query ฐาน — ปลด scope preApproval แล้วเอาเฉพาะคำขอที่ยังไม่จอง */
    private function baseQuery()
    {
        $user = Auth::user();

        $q = Salecar::withoutGlobalScope('preApproval')
            ->where('is_pre_approval', 1)
            ->with(['customer.prefix', 'model', 'subModel', 'saleUser']);

        // sale/lead_sale เห็นเฉพาะของตัวเอง
        if (in_array($user->role, ['sale', 'lead_sale'], true)) {
            $q->where('SaleID', $user->id);
        }

        return $q;
    }

    public function list()
    {
        $canConvert = in_array(Auth::user()->role, self::CONVERT_ROLES, true);

        $rows = $this->baseQuery()->orderByDesc('id')->get();

        $data = $rows->values()->map(function ($s, $i) use ($canConvert) {
            $approved = $s->isApprovedNow();

            if ($approved) {
                $status = "<span class='badge bg-success'>อนุมัติแล้ว</span>";
            } elseif ($s->approval_requested_at) {
                $status = "<span class='badge bg-warning text-dark'>รออนุมัติ</span>";
            } else {
                $status = "<span class='badge bg-secondary'>ยังไม่ขออนุมัติ</span>";
            }

            $customer = trim(implode(' ', array_filter([
                $s->customer?->prefix?->Name_TH,
                $s->customer?->FirstName,
                $s->customer?->LastName,
            ])));

            $editUrl = route('purchase-order.edit', $s->id);
            $action  = "<div class=\"d-flex justify-content-center gap-1\">"
                . "<a href=\"{$editUrl}\" class=\"btn btn-icon btn-warning text-white\" title=\"แก้ไข\"><i class=\"bx bx-edit\"></i></a>";

            // ปุ่มสร้างการจอง — เฉพาะ role ที่อนุญาต และต้องอนุมัติแล้ว
            if ($canConvert) {
                $action .= $approved
                    ? "<button class=\"btn btn-icon btn-success text-white btnConvertBooking\" data-id=\"{$s->id}\" title=\"สร้างการจอง\"><i class=\"bx bx-cart-add\"></i></button>"
                    : "<button class=\"btn btn-icon btn-success text-white\" disabled style=\"opacity:.45;\" title=\"ต้องอนุมัติก่อน\"><i class=\"bx bx-cart-add\"></i></button>";
            }

            // ปุ่มลบ — CONVERT_ROLES ลบได้ทุกคำขอ ; sale ลบได้เฉพาะของตัวเองที่ยังไม่ได้ส่งขออนุมัติ
            if ($this->canDelete($s)) {
                $action .= "<button class=\"btn btn-icon btn-danger text-white btnDeletePreApproval\" data-id=\"{$s->id}\" title=\"ลบคำขอ\"><i class=\"bx bx-trash\"></i></button>";
            }
            $action .= "</div>";

            return [
                'No'        => $i + 1,
                'customer'  => e($customer) ?: '-',
                'model'     => e(trim(($s->model->Name_TH ?? '-') . ' / ' . ($s->subModel->name ?? '-'))),
                'sale'      => e($s->saleUser->name ?? '-'),
                'requested' => $s->approval_requested_at
                    ? Carbon::parse($s->approval_requested_at)->format('d-m-Y')
                    : '-',
                'status'    => $status,
                'Action'    => $action,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /** กด "สร้างการจอง" → แถวเดิมเข้าระบบจอง พร้อมลายเซ็นอนุมัติที่มีอยู่ */
    public function convert($id)
    {
        abort_unless(in_array(Auth::user()->role, self::CONVERT_ROLES, true), 403);

        $saleCar = Salecar::withoutGlobalScope('preApproval')->with('model')->findOrFail($id);

        if (!$saleCar->is_pre_approval) {
            return response()->json(['success' => false, 'message' => 'รายการนี้ถูกสร้างเป็นการจองไปแล้ว'], 422);
        }

        if (!$saleCar->isApprovedNow()) {
            return response()->json(['success' => false, 'message' => 'ยังไม่ได้รับการอนุมัติ — สร้างการจองไม่ได้'], 422);
        }

        $saleCar->update([
            'is_pre_approval'        => false,
            'pre_approval_booked_at' => now(),
            'con_status'             => 1,
            'BookingDate'            => now()->toDateString(), // วันที่กดสร้างการจอง (แก้ได้ในหน้าจอง)
        ]);

        return response()->json(['success' => true, 'message' => 'สร้างการจองเรียบร้อยแล้ว']);
    }

    /** ลบคำขอ (soft delete) */
    public function destroy($id)
    {
        $saleCar = Salecar::withoutGlobalScope('preApproval')->findOrFail($id);

        if (!$saleCar->is_pre_approval) {
            return response()->json([
                'success' => false,
                'message' => 'รายการนี้ถูกสร้างเป็นการจองแล้ว — ลบจากหน้านี้ไม่ได้',
            ], 422);
        }

        abort_unless($this->canDelete($saleCar), 403);

        $saleCar->delete();

        return response()->json(['success' => true, 'message' => 'ลบคำขอเรียบร้อยแล้ว']);
    }
}
