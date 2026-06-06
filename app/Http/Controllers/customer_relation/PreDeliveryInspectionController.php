<?php

namespace App\Http\Controllers\customer_relation;

use App\Exports\preDeliveryInspection\PdiReportExport;
use App\Http\Controllers\Controller;
use App\Models\PreDeliveryInspection;
use App\Models\PreDeliveryInspectionFile;
use App\Models\PreDeliveryInspectionLog;
use App\Models\Salecar;
use App\Services\OneDriveService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PreDeliveryInspectionController extends Controller
{
    public function index()
    {
        return view('customer-relation.pre-delivery-inspection.index');
    }

    public function exportExcel(Request $request)
    {
        $date     = $request->input('date', now()->format('Y-m-d'));
        $filename = 'PDI-ตรวจรถก่อนส่งมอบ-' . $date . '.xlsx';

        return Excel::download(new PdiReportExport($date), $filename);
    }

    public function list(Request $request)
    {
        $salecars = Salecar::with(['customer.prefix', 'saleUser', 'preDeliveryInspection.docs', 'preDeliveryInspection.photos'])
            ->whereNotNull('AdminSignature')
            ->orderByDesc('DeliveryDate')
            ->get();

        $no = 1;
        $data = $salecars->map(function ($s) use (&$no) {
            $c        = $s->customer;
            $fullName = $c
                ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';
            $model = $s->model ? $s->model->Name_TH : '';
            $subModelSale = $s->subModel ? $s->subModel->name : '';
            $subDetail = $s->subModel ? $s->subModel->detail : '';
            $statusSale = $s->conStatus ? $s->conStatus->name : '';

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

            $ins = $s->preDeliveryInspection;
            $hasInspection = $ins !== null;

            // ข้อ 1-4 เรียบร้อยทั้งหมด และข้อ 5-6 มีไฟล์ → ซ่อนออกจากรายการ
            if (
                $ins
                && $ins->accessories_complete == 1
                && $ins->exterior_clean == 1
                && $ins->interior_clean == 1
                && $ins->issues_resolved == 1
                && $ins->docs->isNotEmpty()
                && $ins->photos->isNotEmpty()
            ) {
                return null;
            }

            return [
                'No'            => $no++,
                'salecar_id'    => $s->id,
                'FullName'      => $fullName,
                'sale_name'     => $s->saleUser?->name ?? '-',
                'model'         => $car,
                'delivery_date' => $s->format_delivery_date ?? '-',
                'status_badge'  => $hasInspection
                    ? '<span class="badge rounded-pill bg-success">มีข้อมูลแล้ว</span>'
                    : '<span class="badge rounded-pill bg-warning">ยังไม่มีข้อมูล</span>',
            ];
        });

        return response()->json(['data' => $data->filter()->values()]);
    }

    public function getInspection($salecarId)
    {
        $inspection = PreDeliveryInspection::with(['docs', 'photos'])
            ->where('salecar_id', $salecarId)->first();

        if (!$inspection) {
            return response()->json(null);
        }

        return response()->json([
            'id'                           => $inspection->id,
            'accessories_complete'         => $inspection->accessories_complete,
            'accessories_incomplete_items' => $inspection->accessories_incomplete_items,
            'accessories_note'             => $inspection->accessories_note,
            'exterior_clean'               => $inspection->exterior_clean,
            'exterior_incomplete_items'    => $inspection->exterior_incomplete_items,
            'exterior_note'                => $inspection->exterior_note,
            'interior_clean'               => $inspection->interior_clean,
            'interior_incomplete_items'    => $inspection->interior_incomplete_items,
            'interior_note'                => $inspection->interior_note,
            'issues_resolved'              => $inspection->issues_resolved,
            'issues_detail'                => $inspection->issues_detail,
            'issues_reason'                => $inspection->issues_reason,
            'docs'   => $inspection->docs->map(fn($f) => ['url' => $f->file_url, 'name' => $f->file_name])->values(),
            'photos' => $inspection->photos->map(fn($f) => ['url' => $f->file_url, 'name' => $f->file_name])->values(),
        ]);
    }

    public function save(Request $request, $salecarId)
    {
        $salecar  = Salecar::with('customer')->findOrFail($salecarId);
        $authUser = Auth::user();

        $inspection = PreDeliveryInspection::firstOrNew(['salecar_id' => $salecarId]);
        $accVal = $request->input('accessories_complete');
        $extVal = $request->input('exterior_clean');
        $intVal = $request->input('interior_clean');
        $issVal = $request->input('issues_resolved');

        $inspection->fill([
            'salecar_id'                   => $salecarId,
            'accessories_complete'         => $accVal,
            'accessories_incomplete_items' => $request->input('accessories_incomplete_items'),
            'accessories_note'             => $request->input('accessories_note'),
            'exterior_clean'               => $extVal,
            'exterior_incomplete_items'    => $request->input('exterior_incomplete_items'),
            'exterior_note'                => $request->input('exterior_note'),
            'interior_clean'               => $intVal,
            'interior_incomplete_items'    => $request->input('interior_incomplete_items'),
            'interior_note'                => $request->input('interior_note'),
            'issues_resolved'              => $issVal,
            'issues_detail'                => $request->input('issues_detail'),
            'issues_reason'                => $request->input('issues_reason'),
            'userZone'                     => $authUser->userZone,
            'brand'                        => $authUser->brand,
            'branch'                       => $authUser->branch,
            'UserInsert'                   => Auth::id(),
        ])->save();

        // บันทึก log ถ้ามีข้อที่ไม่เรียบร้อย
        if ($accVal === '0' || $extVal === '0' || $intVal === '0' || $issVal === '0') {
            PreDeliveryInspectionLog::create([
                'inspection_id'                => $inspection->id,
                'salecar_id'                   => $salecarId,
                'accessories_complete'         => $accVal !== null ? (int) $accVal : null,
                'accessories_incomplete_items' => $accVal === '0' ? $request->input('accessories_incomplete_items') : null,
                'exterior_clean'               => $extVal !== null ? (int) $extVal : null,
                'exterior_incomplete_items'    => $extVal === '0' ? $request->input('exterior_incomplete_items') : null,
                'interior_clean'               => $intVal !== null ? (int) $intVal : null,
                'interior_incomplete_items'    => $intVal === '0' ? $request->input('interior_incomplete_items') : null,
                'issues_resolved'              => $issVal !== null ? (int) $issVal : null,
                'issues_detail'                => $issVal === '0' ? $request->input('issues_detail') : null,
                'UserInsert'                   => Auth::id(),
            ]);
        }

        // ── จัดการไฟล์แนบ ──
        $keepDocs   = $request->input('keep_docs',   []);
        $keepPhotos = $request->input('keep_photos', []);

        // ลบไฟล์ที่ user กด X ออก (soft delete)
        $inspection->docs()->whereNotIn('file_url', $keepDocs)->delete();
        $inspection->photos()->whereNotIn('file_url', $keepPhotos)->delete();

        $hasNewDocs   = $request->hasFile('inspection_docs');
        $hasNewPhotos = $request->hasFile('inspection_photos');

        if ($hasNewDocs || $hasNewPhotos) {
            $customer       = $salecar->customer;
            $brandName      = $authUser->brandInfo->name ?? 'Other';
            $customerFolder = ($customer->id ?? 0) . '-' . ($customer->FirstName ?? 'unknown');
            $baseFolder     = "New Car/{$brandName}/Pre-Delivery Inspection (PDI)/{$customerFolder}";

            try {
                $oneDrive = new OneDriveService();

                if ($hasNewDocs) {
                    foreach ($request->file('inspection_docs') as $idx => $file) {
                        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $ext      = $file->getClientOriginalExtension();
                        $fileName = $origName . '_' . $salecarId . '_' . time() . '_' . ($idx + 1) . '.' . $ext;
                        $url      = $oneDrive->upload($file->getRealPath(), $fileName, "{$baseFolder}/Vehicle Inspection Form");
                        PreDeliveryInspectionFile::create([
                            'inspection_id' => $inspection->id,
                            'file_type'     => 'doc',
                            'file_name'     => $file->getClientOriginalName(),
                            'file_url'      => $url,
                        ]);
                    }
                }

                if ($hasNewPhotos) {
                    foreach ($request->file('inspection_photos') as $idx => $file) {
                        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $ext      = $file->getClientOriginalExtension();
                        $fileName = $origName . '_' . $salecarId . '_' . time() . '_' . ($idx + 1) . '.' . $ext;
                        $url      = $oneDrive->upload($file->getRealPath(), $fileName, "{$baseFolder}/Service Consultant");
                        PreDeliveryInspectionFile::create([
                            'inspection_id' => $inspection->id,
                            'file_type'     => 'photo',
                            'file_name'     => $file->getClientOriginalName(),
                            'file_url'      => $url,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
    }

    public function deleteFile(Request $request, $id)
    {
        $inspection = PreDeliveryInspection::findOrFail($id);
        $url        = $request->input('url');

        $inspection->files()->where('file_url', $url)->delete();

        return response()->json(['success' => true]);
    }

    public function proxyFile(Request $request, $inspectionId, $filename = null)
    {
        $inspection = PreDeliveryInspection::findOrFail($inspectionId);
        $shareUrl   = $request->input('url');

        $exists = $inspection->files()->where('file_url', $shareUrl)->exists();

        if (!$exists) {
            abort(403);
        }

        try {
            $oneDrive                  = new OneDriveService();
            ['url' => $downloadUrl, 'name' => $filename] = $oneDrive->getDownloadInfo($shareUrl);

            $guzzle   = new Client(['allow_redirects' => true]);
            $response = $guzzle->get($downloadUrl);

            $contentType = $response->getHeader('Content-Type')[0] ?? 'application/octet-stream';
            $body        = $response->getBody()->getContents();

            return response($body, 200, [
                'Content-Type'        => $contentType,
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
                'Cache-Control'       => 'private, max-age=3600',
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function viewData($salecarId)
    {
        $salecar = Salecar::with([
            'customer.prefix',
            'saleUser',
            'model',
            'subModel',
            'gwmColor',
            'carOrder',
        ])->findOrFail($salecarId);

        $c          = $salecar->customer;
        $inspection = PreDeliveryInspection::with(['docs', 'photos', 'logs'])
            ->where('salecar_id', $salecarId)->first();

        return response()->json([
            'customer' => [
                'full_name' => $c ? trim(($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName) : '-',
                'mobile'    => $c->formatted_mobile ?? '-',
            ],
            'car' => [
                'model'         => $salecar->model?->Name_TH ?? '-',
                'sub_model'     => $salecar->subModel?->name ?? '-',
                'color'         => $salecar->gwmColor?->name ?? ($salecar->Color ?? '-'),
                'year'          => $salecar->Year ?? '-',
                'vin'           => $salecar->carOrder?->vin_number ?? '-',
                'sale_name'     => $salecar->saleUser?->name ?? '-',
                'delivery_date' => $salecar->getFormatDeliveryDateAttribute() ?? '-',
            ],
            'inspection' => $inspection ? [
                'id'                           => $inspection->id,
                'accessories_complete'         => $inspection->accessories_complete,
                'accessories_incomplete_items' => $inspection->accessories_incomplete_items,
                'accessories_note'             => $inspection->accessories_note,
                'exterior_clean'               => $inspection->exterior_clean,
                'exterior_incomplete_items'    => $inspection->exterior_incomplete_items,
                'exterior_note'                => $inspection->exterior_note,
                'interior_clean'               => $inspection->interior_clean,
                'interior_incomplete_items'    => $inspection->interior_incomplete_items,
                'interior_note'                => $inspection->interior_note,
                'issues_resolved'              => $inspection->issues_resolved,
                'issues_detail'                => $inspection->issues_detail,
                'issues_reason'                => $inspection->issues_reason,
                'docs'   => $inspection->docs->map(fn($f) => ['url' => $f->file_url, 'name' => $f->file_name])->values(),
                'photos' => $inspection->photos->map(fn($f) => ['url' => $f->file_url, 'name' => $f->file_name])->values(),
                'logs'   => $inspection->logs->map(fn($l) => [
                    'accessories_complete'         => $l->accessories_complete,
                    'accessories_incomplete_items' => $l->accessories_incomplete_items,
                    'exterior_clean'               => $l->exterior_clean,
                    'exterior_incomplete_items'    => $l->exterior_incomplete_items,
                    'interior_clean'               => $l->interior_clean,
                    'interior_incomplete_items'    => $l->interior_incomplete_items,
                    'issues_resolved'              => $l->issues_resolved,
                    'issues_detail'                => $l->issues_detail,
                    'created_at'                   => $l->created_at?->format('d/m/Y H:i'),
                ])->values(),
            ] : null,
        ]);
    }

}
