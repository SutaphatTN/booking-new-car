<?php

namespace App\Http\Controllers\purchase_order;

use App\Http\Controllers\Controller;
use App\Models\Salecar;
use App\Services\OneDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CancellationController extends Controller
{
    public function index()
    {
        return view('purchase-order.cancellation.view');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $query = Salecar::withTrashed()
            ->with(['customer.prefix', 'model'])
            ->whereIn('con_status', [7, 9])
            ->whereNull('withdraw_date');

        if ($user->role === 'sale') {
            $query->where('SaleID', $user->id);
        }

        $saleCars = $query->get();

        $data = $saleCars->map(function ($s, $index) {
            $c = $s->customer;

            return [
                'No' => $index + 1,
                'FullName' => implode(' ', array_filter([
                    $c?->prefix?->Name_TH ?? null,
                    $c->FirstName ?? null,
                    $c->LastName ?? null,
                ])),
                'model' => $s->model?->Name_TH ?? '-',
                'CancelGCIPDate' => $s->format_cancel_gcip_date ?? '-',
                'Action' => view('purchase-order.cancellation.button', compact('s'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function getData($id)
    {
        $s = Salecar::withTrashed()->with(['customer.prefix', 'model'])->findOrFail($id);
        $c = $s->customer;

        return response()->json([
            'id' => $s->id,
            'FullName' => implode(' ', array_filter([
                $c?->prefix?->Name_TH ?? null,
                $c->FirstName ?? null,
                $c->LastName ?? null,
            ])),
            'model' => $s->model?->Name_TH ?? '-',
            'CancelGCIPDate' => $s->CancelGCIPDate,
            'RefundDate' => $s->RefundDate,
            'RefundMotorDate' => $s->RefundMotorDate,
            'withdraw_attachments' => $s->withdraw_attachment_url ?? [],
        ]);
    }

    public function confirmWithdraw($id)
    {
        try {
            $saleCar = Salecar::withTrashed()->findOrFail($id);
            $saleCar->withdraw_user = Auth::id();
            $saleCar->withdraw_date = now();
            $saleCar->save();

            return response()->json(['success' => true, 'message' => 'ยืนยันการคืนเงินเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uploadWithdrawAttachment(Request $request, $id)
    {
        try {
            $saleCar = Salecar::withTrashed()->with('customer')->findOrFail($id);
            $customer = $saleCar->customer;

            $brandName = Auth::user()->brandInfo->name ?? 'Other';
            $customerFolder = $customer->id . '-' . ($customer->FirstName ?? 'unknown');
            $folder = "New Car/{$brandName}/หลักฐานการคืนเงินถอนจอง/{$customerFolder}";

            $oneDrive = new OneDriveService();
            $urls = $saleCar->withdraw_attachment_url ?? [];

            foreach ($request->file('attachments', []) as $index => $file) {
                $fileName = 'withdraw_' . $saleCar->id . '_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $urls[] = $oneDrive->upload($file->getRealPath(), $fileName, $folder);
            }

            $saleCar->withdraw_attachment_url = $urls;
            $saleCar->save();

            return response()->json(['success' => true, 'attachments' => $urls]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteWithdrawAttachment(Request $request, $id)
    {
        try {
            $saleCar = Salecar::withTrashed()->findOrFail($id);
            $urlToRemove = $request->url;

            $urls = collect($saleCar->withdraw_attachment_url ?? [])
                ->reject(fn($u) => $u === $urlToRemove)
                ->values()
                ->all();

            $saleCar->withdraw_attachment_url = $urls;
            $saleCar->save();

            return response()->json(['success' => true, 'attachments' => $urls]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateRefund(Request $request, $id)
    {
        try {
            $saleCar = Salecar::withTrashed()->findOrFail($id);
            $saleCar->RefundDate = $request->refund_date;
            $saleCar->save();

            return response()->json(['success' => true, 'message' => 'บันทึกวันที่คืนเงินเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $saleCar = Salecar::withTrashed()->findOrFail($id);
            $saleCar->CancelGCIPDate = $request->cancel_gcip_date;
            $saleCar->RefundDate = $request->refund_date;
            $saleCar->RefundMotorDate = $request->refund_motor_date;
            $saleCar->save();

            return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }
}
