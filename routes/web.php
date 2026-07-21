<?php

use App\Http\Controllers\accessory\AccessoryController;
use App\Http\Controllers\auth\ForgotController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\campaign\CampaignController;
use App\Http\Controllers\campaign\CampaignClaimController;
use App\Http\Controllers\campaign\CampaignApprovalController;
use App\Http\Controllers\car_order\CarOrderController;
use App\Http\Controllers\car_order\WsImportController;
use App\Http\Controllers\color\ColorController;
use App\Http\Controllers\customer\CustomerController;
use App\Http\Controllers\finance\FinanceController;
use App\Http\Controllers\floor_plan\FloorPlanController;
use App\Http\Controllers\forecast\ForecastController;
use App\Http\Controllers\dbar\DbarController;
use App\Http\Controllers\home\HomeController;
use App\Http\Controllers\model_car\ModelCarController;
use App\Http\Controllers\model_car\SubModelCarController;
use App\Http\Controllers\purchase_order\CancellationController;
use App\Http\Controllers\purchase_order\PurchaseOrderController;
use App\Http\Controllers\vehicle\LicenseController;
use App\Http\Controllers\vehicle\VehicleController;
use App\Http\Controllers\BrandSwitchController;
use App\Http\Controllers\BranchSwitchController;
use App\Http\Controllers\delivery_form\DeliveryFormController;
use App\Http\Controllers\invoice\InvoiceController;
use App\Http\Controllers\pricelist_car\PricelistCarController;
use App\Http\Controllers\customer_tracking\CustomerTrackingController;
use App\Http\Controllers\source\SourceController;
use App\Http\Controllers\insurance\InsuranceController;
use App\Http\Controllers\gwm_incentive\GwmIncentiveController;
use App\Http\Controllers\stock_film\FilmPriceListController;
use App\Http\Controllers\stock_film\FilmSettingController;
use App\Http\Controllers\stock_film\FilmUsageController;
use App\Http\Controllers\stock_film\StockFilmController;
use App\Http\Controllers\service_check_tracking\ServiceCheckTrackingController;
use App\Http\Controllers\customer_relation\PreDeliveryInspectionController;
use App\Http\Controllers\customer_relation\SsiController;
use App\Http\Controllers\marketing\AdController;
use App\Http\Controllers\pre_approval\PreApprovalController;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'index'])
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// ลงทะเบียน (สร้าง user) — ต้อง login และเป็น role ที่มีสิทธิ์เท่านั้น
Route::middleware(['auth', 'role:audit,audit_lead,audit_dp,gm,admin,manager'])->group(function () {
    Route::resource('register', RegisterController::class)->only(['index', 'store']);
});
Route::resource('forgot', ForgotController::class);

// อนุมัติสถานที่ผ่านลิงก์ในเมล (ไม่ต้อง login — ใช้ token เป็นความลับ)
Route::get('source/approval/{token}', [SourceController::class, 'showApproval'])->name('source.approval');
Route::post('source/approval/{token}/approve', [SourceController::class, 'approve'])->name('source.approval.approve');
Route::post('source/approval/{token}/reject', [SourceController::class, 'reject'])->name('source.approval.reject');

// อนุมัติใบจองผ่านลิงก์ในเมล (ไม่ต้อง login — ใช้ token)
Route::get('purchase-order/approval/{token}', [PurchaseOrderController::class, 'emailApprove'])->name('purchase-order.emailApprove');
// ดูรายละเอียด (PDF สรุปการขาย) แบบ read-only ผ่าน token — ไม่มีปุ่มอนุมัติ
Route::get('purchase-order/approval/{token}/summary', [PurchaseOrderController::class, 'emailSummary'])->name('purchase-order.emailSummary');
Route::post('purchase-order/approval/{token}/manager', [PurchaseOrderController::class, 'managerApprove'])->name('purchase-order.managerApprove');
Route::post('purchase-order/approval/{token}/gm-decide', [PurchaseOrderController::class, 'gmDecide'])->name('purchase-order.gmDecide');
Route::post('purchase-order/approval/{token}/final', [PurchaseOrderController::class, 'finalApprove'])->name('purchase-order.finalApprove');
// ตีกลับใบจอง (ทุกขั้น) — ปลายทางตามขั้นที่กด (admin+เซลล์ / ผู้จัดการ / GM)
Route::post('purchase-order/approval/{token}/return', [PurchaseOrderController::class, 'returnApproval'])->name('purchase-order.returnApproval');

// อนุมัติแคมเปญ CK ผ่านลิงก์ในเมล (ไม่ต้อง login — ใช้ token)
Route::get('campaign-approval/{token}', [CampaignApprovalController::class, 'emailApprove'])->name('campaign.ckApproval.email');
Route::post('campaign-approval/{token}/approve', [CampaignApprovalController::class, 'approve'])->name('campaign.ckApproval.approve');
Route::post('campaign-approval/{token}/reject', [CampaignApprovalController::class, 'reject'])->name('campaign.ckApproval.reject');

Route::get('/keep-alive', function () {
    if (!Auth::check()) {
        return response()->json(['status' => 'expired'], 401)
            ->header('Cache-Control', 'no-store');
    }
    request()->session()->put('last_keep_alive', now());
    return response()->json(['status' => 'ok'])
        ->header('Cache-Control', 'no-store');
});

Route::middleware('auth')->group(function () {
    Route::get('/accessory/search', [PurchaseOrderController::class, 'searchAccessory'])->name('accessory.search');
    Route::post('/switch-brand', [BrandSwitchController::class, 'switch'])->name('brand.switch');
    Route::post('/switch-brand/reset', [BrandSwitchController::class, 'reset'])->name('brand.reset');
    Route::post('/switch-branch', [BranchSwitchController::class, 'switch'])->name('branch.switch');
    Route::post('/switch-branch/reset', [BranchSwitchController::class, 'reset'])->name('branch.reset');
});

Route::middleware(['auth', 'notsale'])->group(function () {
    // invoice
    Route::get('invoice/list', [InvoiceController::class, 'list'])->name('invoice.list');
    Route::get('invoice/{id}/pdf', [InvoiceController::class, 'pdf'])->name('invoice.pdf');
    Route::post('invoice/{id}/approve', [InvoiceController::class, 'approve'])->name('invoice.approve');
    Route::post('invoice/{id}/confirm-receipt', [InvoiceController::class, 'confirmReceipt'])->name('invoice.confirmReceipt');

    //report 
    Route::get('invoice/view-export-report', [InvoiceController::class, 'viewExportReport'])->name('invoice.view-export-report');
    Route::get('/invoice/report-export', [InvoiceController::class, 'exportReport'])->name('invoice.report-export');


    // purchase-order
    Route::get('purchase-order/viewFN', [FinanceController::class, 'viewFN'])->name('purchase-order.viewFN');
    Route::get('purchase-order/{id}/view-more', [FinanceController::class, 'viewMoreFN'])->name('purchase-order.viewMoreFN');
    Route::get('purchase-order/list-fn', [FinanceController::class, 'listFN']);
    Route::get('purchase-order/edit-fn/{id}', [FinanceController::class, 'editFN'])->name('purchase-order.editFN');
    Route::put('purchase-order/update-fn/{id}', [FinanceController::class, 'updateFN'])->name('purchase-order.updateFN');
    Route::delete('purchase-order/destroy-fn/{id}', [FinanceController::class, 'destroyFN'])->name('purchase-order.destroyFN');
    Route::get('/purchase-order/booking-export', [PurchaseOrderController::class, 'exportBooking'])->name('purchase-order.booking-export');
    //commission sale report
    Route::get('purchase-order/view-export-commission', [PurchaseOrderController::class, 'viewExportCommission'])->name('purchase-order.view-export-commission');
    Route::get('/purchase-order/sale-com-export', [PurchaseOrderController::class, 'exportSaleCom'])->name('purchase-order.sale-com-export');
    //GP Report
    Route::get('purchase-order/view-export-gp', [PurchaseOrderController::class, 'viewExportGP'])->name('purchase-order.view-export-gp');
    Route::get('/purchase-order/gp-export', [PurchaseOrderController::class, 'exportGP'])->name('purchase-order.gp-export');
    // sale car Estimated report
    Route::get('purchase-order/view-export-saleCar', [PurchaseOrderController::class, 'viewExportSaleCar'])->name('purchase-order.view-export-saleCar');
    Route::get('/purchase-order/saleCar-export', [PurchaseOrderController::class, 'exportSaleCar'])->name('purchase-order.saleCar-export');
    // ประมาณการเซลล์ (กรองเดือนตาม DeliveryInCKDate, Normal + Test Drive)
    Route::get('purchase-order/view-export-saleEstimate', [PurchaseOrderController::class, 'viewExportSaleEstimate'])->name('purchase-order.view-export-saleEstimate');
    Route::get('/purchase-order/saleEstimate-export', [PurchaseOrderController::class, 'exportSaleEstimate'])->name('purchase-order.saleEstimate-export');
    //sale car booking report 
    Route::get('purchase-order/view-export-saleBooking', [PurchaseOrderController::class, 'viewExportSaleBooking'])->name('purchase-order.view-export-saleBooking');
    Route::get('/purchase-order/saleBooking-export', [PurchaseOrderController::class, 'exportSaleBooking'])->name('purchase-order.saleBooking-export');
    //report gwm stock
    Route::get('purchase-order/view-export-gwm-stock', [PurchaseOrderController::class, 'viewExportGwmStock'])->name('purchase-order.view-export-gwm-stock');
    Route::get('/purchase-order/gwm-stock-export', [PurchaseOrderController::class, 'gwmStockExport'])->name('purchase-order.gwm-stock-export');
    //insurance report (ข้อมูลประกันภัย) — เฉพาะ admin ดึงตามเดือน DeliveryDate ทุก brand แยก sheet
    Route::get('purchase-order/view-export-insurance', [PurchaseOrderController::class, 'viewExportInsurance'])->name('purchase-order.view-export-insurance');
    Route::get('/purchase-order/insurance-export', [PurchaseOrderController::class, 'exportInsurance'])->name('purchase-order.insurance-export');
    //lead online allocation report (จัดสรร Lead Online) — admin/gm/md/manager ดึงทุก brand แยก sheet
    Route::get('purchase-order/view-export-lead-online', [PurchaseOrderController::class, 'viewExportLeadOnline'])->name('purchase-order.view-export-lead-online');
    Route::get('/purchase-order/lead-online-export', [PurchaseOrderController::class, 'exportLeadOnline'])->name('purchase-order.lead-online-export');
    //over budget report (รายงานเกินงบ) — admin/md/account/gm ทุก brand แยก sheet ; manager/audit brand ตัวเอง (1→1,3)
    Route::get('purchase-order/view-export-over-budget', [PurchaseOrderController::class, 'viewExportOverBudget'])->name('purchase-order.view-export-over-budget');
    Route::get('/purchase-order/over-budget-export', [PurchaseOrderController::class, 'exportOverBudget'])->name('purchase-order.over-budget-export');
    //delivery report
    Route::get('purchase-order/view-export-monthlyDelivery', [PurchaseOrderController::class, 'viewExportMonthlyDelivery'])->name('purchase-order.view-export-monthlyDelivery');
    Route::get('/purchase-order/monthlyDelivery-export', [PurchaseOrderController::class, 'exportMonthlyDelivery'])->name('purchase-order.monthlyDelivery-export');
    // car order stock report
    Route::get('car-order/view-export-stock', [CarOrderController::class, 'viewExportStock'])->name('car-order.view-export-stock');
    Route::get('car-order/stock-export', [CarOrderController::class, 'exportStock'])->name('car-order.stock-export');
    Route::get('car-order/data-export', [CarOrderController::class, 'dataExport'])->name('car-order.data-export');

    //accessory partner
    Route::get('accessory/partner', [AccessoryController::class, 'viewPartner'])->name('accessory.partner');
    Route::get('accessory/partner/list', [AccessoryController::class, 'listPartner']);
    Route::get('accessory/create-partner', [AccessoryController::class, 'createPartner']);
    Route::post('accessory/store-partner', [AccessoryController::class, 'storePartner'])->name('accessory.storePartner');
    Route::get('accessory/edit-partner/{id}', [AccessoryController::class, 'editPartner'])->name('accessory.editPartner');
    Route::put('accessory/update-partner/{id}', [AccessoryController::class, 'updatePartner'])->name('accessory.updatePartner');
    Route::delete('accessory/destroy-partner/{id}', [AccessoryController::class, 'destroyPartner'])->name('accessory.destroyPartner');
    //accessory
    Route::get('accessory/list', [AccessoryController::class, 'listAccessory']);
    Route::get('/api/accessory/sub-model/{model_id}', [AccessoryController::class, 'getSubModelAcc']);
    Route::get('accessory/{id}/view-more', [AccessoryController::class, 'viewMore'])->name('accessory.viewMore');
    Route::post('/accessory/status-acc', [AccessoryController::class, 'statusAcc'])->name('accessory.status-acc');
    //export accessory partner
    Route::get('accessory/view-export-accessory', [AccessoryController::class, 'viewExportAccessory'])->name('accessory.view-export-accessory');
    Route::get('/accessory/accessory-partner-export', [AccessoryController::class, 'exportAccessoryPartner'])->name('accessory.accessory-partner-export');

    //marketing : แอด (คลิปที่ยิงแอด) — เฉพาะ admin/adminPage, แยก brand+branch
    Route::get('marketing/ad', [AdController::class, 'index'])->name('ad.index');
    Route::get('marketing/ad/list', [AdController::class, 'list'])->name('ad.list');
    Route::post('marketing/ad/store', [AdController::class, 'store'])->name('ad.store');
    Route::get('marketing/ad/{id}/edit', [AdController::class, 'edit'])->name('ad.edit');
    Route::put('marketing/ad/{id}', [AdController::class, 'update'])->name('ad.update');
    Route::patch('marketing/ad/{id}/archive', [AdController::class, 'archive'])->name('ad.archive');
    Route::patch('marketing/ad/{id}/restore', [AdController::class, 'restore'])->name('ad.restore');

    //source (แหล่งที่มา) — แยกหน้า แหล่งที่มาย่อย + สถานที่
    Route::get('source', [SourceController::class, 'index'])->name('source.index');
    // sub-source (แหล่งที่มาย่อย) — เพิ่ม/แก้ (ไม่มีลบ เพราะใช้ร่วมกับ Purchase Order)
    Route::get('source/sub', [SourceController::class, 'subIndex'])->name('source.sub.index');
    Route::get('source/place', [SourceController::class, 'placeIndex'])->name('source.place.index');
    Route::get('source/place/report', [SourceController::class, 'reportMonthly'])->name('source.place.report');
    Route::get('source/sub/list', [SourceController::class, 'listSub']);
    Route::get('source/sub/create', [SourceController::class, 'createSub']);
    Route::post('source/sub/store', [SourceController::class, 'storeSub'])->name('source.sub.store');
    Route::get('source/sub/edit/{id}', [SourceController::class, 'editSub'])->name('source.sub.edit');
    Route::put('source/sub/update/{id}', [SourceController::class, 'updateSub'])->name('source.sub.update');
    Route::delete('source/sub/destroy/{id}', [SourceController::class, 'destroySub'])->name('source.sub.destroy');
    // place (สถานที่) — CRUD เต็ม
    Route::get('source/place/list', [SourceController::class, 'listPlace']);
    Route::get('source/place/create', [SourceController::class, 'createPlace']);
    Route::post('source/place/store', [SourceController::class, 'storePlace'])->name('source.place.store');
    Route::get('source/place/edit/{id}', [SourceController::class, 'editPlace'])->name('source.place.edit');
    Route::put('source/place/update/{id}', [SourceController::class, 'updatePlace'])->name('source.place.update');
    Route::delete('source/place/destroy/{id}', [SourceController::class, 'destroyPlace'])->name('source.place.destroy');
    // เคลียร์ค่าใช้จ่ายของสถานที่ + อนุมัติการจ่าย (บัญชี)
    Route::get('source/place/{id}/clear/pdf', [SourceController::class, 'clearPdf'])->name('source.place.clear.pdf');
    Route::get('source/place/{id}/clear', [SourceController::class, 'clearForm']);
    Route::post('source/place/{id}/clear', [SourceController::class, 'storeClear'])->name('source.place.clear.store');
    Route::post('source/place/{id}/clear/approve-pay', [SourceController::class, 'approveClearPay'])->name('source.place.clear.approve');
    Route::delete('source/place/{id}/clear/{clearId}', [SourceController::class, 'destroyClear'])->name('source.place.clear.destroy');
    // ปิดยอด/จบงาน + เปิดใหม่ (บัญชี)
    Route::post('source/place/{id}/settle', [SourceController::class, 'settlePlace'])->name('source.place.settle');
    Route::post('source/place/{id}/reopen', [SourceController::class, 'reopenPlace'])->name('source.place.reopen');
    // ขออนุมัติสถานที่ (batch) — ส่งเมลหา MD
    Route::post('source/request', [SourceController::class, 'storeRequest'])->name('source.request.store');
    // ขออนุมัติเพิ่ม (topup งบประมาณของสถานที่ที่อนุมัติแล้ว)
    Route::post('source/place/{id}/topup', [SourceController::class, 'storeTopupRequest'])->name('source.place.topup');

    //insurance (ประกัน) — ตั้งค่า: เพิ่ม/แก้/ลบ เฉพาะ admin, role อื่นดูตารางได้
    Route::get('insurance', [InsuranceController::class, 'index'])->name('insurance.index');
    Route::get('insurance/list', [InsuranceController::class, 'list']);
    Route::get('insurance/create', [InsuranceController::class, 'create']);
    Route::post('insurance/store', [InsuranceController::class, 'store'])->name('insurance.store');
    Route::get('insurance/edit/{id}', [InsuranceController::class, 'edit'])->name('insurance.edit');
    Route::put('insurance/update/{id}', [InsuranceController::class, 'update'])->name('insurance.update');
    Route::delete('insurance/destroy/{id}', [InsuranceController::class, 'destroy'])->name('insurance.destroy');

    //campaign
    Route::get('campaign/list', [CampaignController::class, 'listCampaign']);
    // อนุมัติแคมเปญ CK (type = 4) — รายเดือน
    Route::get('campaign/ck-approval', [CampaignApprovalController::class, 'index'])->name('campaign.ckApproval');
    Route::get('campaign/ck-approval/list', [CampaignApprovalController::class, 'list']);
    Route::get('campaign/ck-approval/pending-list', [CampaignApprovalController::class, 'pendingList']);
    Route::get('campaign/ck-approval/model-options', [CampaignApprovalController::class, 'modelOptions']);
    Route::post('campaign/ck-approval/request', [CampaignApprovalController::class, 'requestApproval'])->name('campaign.ckApproval.request');
    Route::get('campaign/{id}/view-more', [CampaignController::class, 'viewMore'])->name('campaign.viewMore');
    Route::post('/campaign/status-cam', [CampaignController::class, 'statusCam'])->name('campaign.status-cam');
    Route::post('/campaign/{id}/archive', [CampaignController::class, 'archiveCam'])->name('campaign.archive');
    Route::get('/api/campaign/sub-model/{model_id}', [CampaignController::class, 'getSubModelCam']);
    //campaign claim (รายการใช้แคมเปญ On-Top)
    Route::get('campaign/claim', [CampaignClaimController::class, 'index'])->name('campaign.claim');
    Route::get('campaign/claim/list', [CampaignClaimController::class, 'listClaim']);
    Route::get('campaign/claim/report', [CampaignClaimController::class, 'exportReport'])->name('campaign.claim.report');
    Route::get('campaign/claim/{id}/edit', [CampaignClaimController::class, 'editClaim'])->name('campaign.claim.edit');
    Route::post('campaign/claim/{id}/update', [CampaignClaimController::class, 'updateClaim'])->name('campaign.claim.update');
    //name campaign
    Route::get('campaign/appellation', [CampaignController::class, 'viewAppellation'])->name('campaign.appellation');
    Route::get('campaign/appellation/list', [CampaignController::class, 'listAppellation']);
    Route::get('campaign/create-appellation', [CampaignController::class, 'createAppellation']);
    Route::post('campaign/store-appellation', [CampaignController::class, 'storeAppellation'])->name('campaign.storeAppellation');
    Route::get('campaign/edit-appellation/{id}', [CampaignController::class, 'editAppellation'])->name('campaign.editAppellation');
    Route::put('campaign/update-appellation/{id}', [CampaignController::class, 'updateAppellation'])->name('campaign.updateAppellation');
    Route::delete('campaign/destroy-appellation/{id}', [CampaignController::class, 'destroyAppellation'])->name('campaign.destroyAppellation');

    // user (จัดการผู้ใช้งาน)
    //  - อ่าน (admin + audit_lead)
    Route::middleware('usermanage:read')->group(function () {
        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/list', [UserController::class, 'listUser']);
        Route::get('user/{id}/view-more', [UserController::class, 'viewMore'])->name('user.viewMore');
    });
    //  - แก้ไข/ลบ (admin เท่านั้น — audit_lead ดูได้อย่างเดียว)
    Route::middleware('usermanage:write')->group(function () {
        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::put('user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    //finance
    Route::get('finance/list', [FinanceController::class, 'listFinance']);
    //finance extra com
    Route::get('finance/extra-com', [FinanceController::class, 'viewExtraCom'])->name('finance.extra-com');
    Route::get('finance/extra-com/list', [FinanceController::class, 'listExtraCom']);
    Route::get('finance/create-extra-com', [FinanceController::class, 'createExtraCom']);
    Route::post('finance/store-extra-com', [FinanceController::class, 'storeExtraCom'])->name('finance.store-extra-com');
    Route::get('finance/edit-extra-com/{id}', [FinanceController::class, 'editExtraCom'])->name('finance.edit-extra-com');
    Route::put('finance/update-extra-com/{id}', [FinanceController::class, 'updateExtraCom'])->name('finance.update-extra-com');
    Route::delete('finance/destroy-extra-com/{id}', [FinanceController::class, 'destroyExtraCom'])->name('finance.destroy-extra-com');
    //FirmFinance Report
    Route::get('finance/view-export-firm', [FinanceController::class, 'viewExportFirm'])->name('finance.view-export-firm');
    Route::get('/finance/firm-export', [FinanceController::class, 'exportFirm'])->name('finance.firm-export');

    //pricelist-car
    Route::get('pricelist-car/list', [PricelistCarController::class, 'listPricelist']);
    Route::get('/api/pricelist-car/sub-model/{model_id}', [PricelistCarController::class, 'getSubModel']);

    //car
    Route::get('model-car/list', [ModelCarController::class, 'listCar']);
    Route::get('sub-model-car/list', [SubModelCarController::class, 'listSubCar']);
    Route::get('sub-model-car/{id}/view-more', [SubModelCarController::class, 'viewMore'])->name('sub-model-car.viewMore');
    Route::post('/sub-model-car/status-sub-car', [SubModelCarController::class, 'statusSubCar'])->name('sub-model-car.status-sub-car');

    //car-order : import WS (การตั้งค่า -> ข้อมูลรถ -> นำเข้า WS)
    Route::get('car-order/import-ws', [WsImportController::class, 'index'])->name('car-order.import-ws');
    Route::get('car-order/import-ws/template', [WsImportController::class, 'template'])->name('car-order.import-ws.template');
    Route::post('car-order/import-ws', [WsImportController::class, 'import'])->name('car-order.import-ws.store');

    //car-order
    Route::get('car-order/list', [CarOrderController::class, 'listCarOrder']);
    Route::get('car-order/{id}/view-more', [CarOrderController::class, 'viewMore'])->name('car-order.viewMore');
    Route::get('/api/car-order/sub-model', [CarOrderController::class, 'getSubModelCarOrder']);
    Route::get('/car-order/search', [CarOrderController::class, 'search'])->name('car-order.search');
    //car-order history
    Route::get('car-order/history', [CarOrderController::class, 'history'])->name('car-order.history');
    Route::get('car-order/history/list', [CarOrderController::class, 'listHistory']);
    //car-order pending
    Route::get('car-order/pending', [CarOrderController::class, 'pending'])->name('car-order.pending');
    Route::get('car-order/pending/list', [CarOrderController::class, 'listPending']);
    Route::get('car-order/edit-pending/{id}', [CarOrderController::class, 'editPending'])->name('car-order.editPending');
    Route::put('car-order/update-pending/{id}', [CarOrderController::class, 'updatePending'])->name('car-order.updatePending');
    Route::delete('car-order/destroy-pending/{id}', [CarOrderController::class, 'destroyPending']);
    // waiting
    Route::post('car-order/store-waiting', [CarOrderController::class, 'storeWaiting'])->name('car-order.storeWaiting');
    Route::get('car-order/view-waiting/{id}', [CarOrderController::class, 'viewWaiting'])->name('car-order.viewWaiting');
    Route::get('car-order/edit-waiting/{id}', [CarOrderController::class, 'editWaiting'])->name('car-order.editWaiting');
    Route::put('car-order/update-waiting/{id}', [CarOrderController::class, 'updateWaiting'])->name('car-order.updateWaiting');
    Route::delete('car-order/destroy-waiting/{id}', [CarOrderController::class, 'destroyWaiting'])->name('car-order.destroyWaiting');
    Route::post('car-order/approve-waiting/{id}', [CarOrderController::class, 'approveWaiting'])->name('car-order.approveWaiting');
    Route::post('car-order/reject-waiting/{id}', [CarOrderController::class, 'rejectWaiting'])->name('car-order.rejectWaiting');
    //car-order process
    Route::get('car-order/process', [CarOrderController::class, 'process'])->name('car-order.process');
    Route::get('car-order/process/list', [CarOrderController::class, 'listProcess']);
    Route::get('car-order/edit-process/{id}', [CarOrderController::class, 'editProcess'])->name('car-order.editProcess');
    Route::put('car-order/update-process/{id}', [CarOrderController::class, 'updateProcess'])->name('car-order.updateProcess');
    //car-order approve
    Route::get('car-order/approve', [CarOrderController::class, 'approve'])->name('car-order.approve');
    Route::get('car-order/approve/list', [CarOrderController::class, 'listApprove']);
    // ขออนุมัติที่เลือก (ส่งเมลรวม) + อนุมัติที่เลือก (bulk)
    Route::post('car-order/process/request-approval', [CarOrderController::class, 'requestApproval'])->name('car-order.requestApproval');
    Route::post('car-order/process/bulk-approve', [CarOrderController::class, 'bulkApprove'])->name('car-order.bulkApprove');
    Route::get('car-order/edit-approve/{id}', [CarOrderController::class, 'editApprove'])->name('car-order.editApprove');
    Route::put('car-order/update-approve/{id}', [CarOrderController::class, 'updateApprove'])->name('car-order.updateApprove');
    // waiting
    Route::get('car-order/edit-approve-waiting/{id}', [CarOrderController::class, 'editApproveWaiting'])->name('car-order.editApproveWaiting');
    Route::put('car-order/update-approve-waiting/{id}', [CarOrderController::class, 'updateApproveWaiting'])->name('car-order.updateApproveWaiting');
    Route::delete('car-order/destroy-approve/{id}', [CarOrderController::class, 'destroyApprove']);
    // รับทราบรายการไม่อนุมัติ (soft delete ออกจากหน้าผลการอนุมัติ)
    Route::delete('car-order/acknowledge-reject/{id}', [CarOrderController::class, 'acknowledgeReject'])->name('car-order.acknowledgeReject');
    Route::delete('car-order/acknowledge-reject-waiting/{id}', [CarOrderController::class, 'acknowledgeRejectWaiting'])->name('car-order.acknowledgeRejectWaiting');
    //condition select ca model
    Route::get('/api/car-order/models-by-customer', [CarOrderController::class, 'getModelsByCustomer']);

    // Floor Plan (เห็นเฉพาะ admin, audit_internal — ตรวจสิทธิ์ใน controller)
    Route::get('floor-plan/interest-rate', [FloorPlanController::class, 'interestRate'])->name('floor-plan.interest-rate');
    Route::put('floor-plan/interest-rate', [FloorPlanController::class, 'updateInterestRate'])->name('floor-plan.interest-rate.update');
    Route::get('floor-plan/fp', [FloorPlanController::class, 'fpList'])->name('floor-plan.fp');
    Route::get('floor-plan/fp/export', [FloorPlanController::class, 'exportFp'])->name('floor-plan.fp.export');
    Route::put('floor-plan/fp/{id}/close-date', [FloorPlanController::class, 'updateFpCloseDate'])->name('floor-plan.fp.close-date');
    Route::get('floor-plan/dispose', [FloorPlanController::class, 'disposeList'])->name('floor-plan.dispose');
    Route::get('floor-plan/dispose/export', [FloorPlanController::class, 'exportDispose'])->name('floor-plan.dispose.export');
    Route::put('floor-plan/dispose/{id}', [FloorPlanController::class, 'updateDispose'])->name('floor-plan.dispose.update');

    //color
    Route::get('color/list', [ColorController::class, 'listColor']);
    Route::get('/api/color/sub-model/{model_id}', [ColorController::class, 'getSubModelColorSub']);

    //license ป้ายแดง
    Route::get('license/list', [LicenseController::class, 'listLicense']);
    Route::get('license/loan-options', [LicenseController::class, 'loanOptions']);
    Route::post('license/loan', [LicenseController::class, 'storeLoan']);
    Route::post('license/loan/{id}/return', [LicenseController::class, 'returnLoan']);
    Route::get('license/{id}/view-more', [LicenseController::class, 'viewMore'])->name('vehicle.license.viewMore');
    Route::post('/license/approve-finance', [LicenseController::class, 'approveFinance']);

    Route::get('/license/stock-export', [LicenseController::class, 'exportLicStock'])->name('license.stock-export');
    Route::get('/license/loan-export', [LicenseController::class, 'exportLicLoan'])->name('license.loan-export');
    Route::get('license/view-export-license', [LicenseController::class, 'viewExportLicense'])->name('license.view-export-license');
    Route::get('/license/summary-export', [LicenseController::class, 'exportLicSummary'])->name('license.summary-export');


    //license ป้ายทะเบียน
    Route::get('vehicle/list', [VehicleController::class, 'listVehicle']);
    Route::get('vehicle/{id}/view-more', [VehicleController::class, 'viewMore'])->name('vehicle.viewMore');
    Route::post('/vehicle/update-vehicle', [VehicleController::class, 'updateVehicle']);

    Route::get('vehicle/withdrawal-pending', [VehicleController::class, 'withdrawalPending'])->name('vehicle.withdrawal-pending');
    Route::post('/vehicle/confirm-withdrawal', [VehicleController::class, 'confirmWithdrawal']);
    Route::get('/vehicle/export-pdf', [VehicleController::class, 'exportPdf']);

    Route::post('/vehicle/confirm-clear', [VehicleController::class, 'confirmClear']);
    Route::get('/vehicle/export-clear-pdf', [VehicleController::class, 'exportClearPdf']);
    // ประวัติส่งเบิก/เคลียร์ (รายชุด) — re-export ทั้งชุด (เฉพาะ admin, registration)
    Route::get('vehicle/history', [VehicleController::class, 'history'])->name('vehicle.history')->middleware('role:admin,registration');
    //export vehicle
    Route::get('vehicle/view-export-vehicle', [VehicleController::class, 'viewExportVehicle'])->name('vehicle.view-export-vehicle');
    Route::get('/vehicle/vehicle-export', [VehicleController::class, 'exportVehicle'])->name('vehicle.vehicle-export');
    Route::get('/vehicle/export-license-plate', [VehicleController::class, 'exportLicensePlate'])->name('vehicle.export-license-plate');

    // delivery-form
    Route::get('delivery-form', [DeliveryFormController::class, 'index'])->name('delivery-form.index');
    Route::get('delivery-form/search', [DeliveryFormController::class, 'search'])->name('delivery-form.search');
    Route::get('delivery-form/{id}', [DeliveryFormController::class, 'show'])->name('delivery-form.show');

    Route::resource('accessory', AccessoryController::class);
    Route::resource('campaign', CampaignController::class);
    Route::resource('finance', FinanceController::class);
    Route::resource('car-order', CarOrderController::class);
    Route::resource('vehicle', VehicleController::class);
    Route::resource('invoice', InvoiceController::class);

    Route::resource('license', LicenseController::class)->names([
        'index' => 'vehicle.license.index',
        'create' => 'vehicle.license.create',
        'store' => 'vehicle.license.store',
        'edit' => 'vehicle.license.edit',
        'update' => 'vehicle.license.update',
        'destroy' => 'vehicle.license.destroy',
    ]);

    Route::resource('model-car', ModelCarController::class)->names([
        'index' => 'model-car.index',
        'create' => 'model-car.create',
        'store' => 'model-car.store',
        'edit' => 'model-car.edit',
        'update' => 'model-car.update',
        'destroy' => 'model-car.destroy',
    ]);

    Route::resource('sub-model-car', SubModelCarController::class)->names([
        'index' => 'model.sub-model.index',
        'create' => 'model.sub-model.create',
        'store' => 'model.sub-model.store',
        'edit' => 'model.sub-model.edit',
        'update' => 'model.sub-model.update',
        'destroy' => 'model.sub-model.destroy',
    ]);

    Route::resource('color', ColorController::class)->names([
        'index' => 'model.color.index',
        'create' => 'model.color.create',
        'store' => 'model.color.store',
        'edit' => 'model.color.edit',
        'update' => 'model.color.update',
        'destroy' => 'model.color.destroy',
    ]);

    Route::resource('pricelist-car', PricelistCarController::class)->names([
        'index'   => 'model.pricelist-car.index',
        'create'  => 'model.pricelist-car.create',
        'store'   => 'model.pricelist-car.store',
        'edit' => 'model.pricelist-car.edit',
        'update' => 'model.pricelist-car.update',
        'destroy' => 'model.pricelist-car.destroy',
    ]);

    // gwm-incentive
    Route::get('gwm-incentive/list', [GwmIncentiveController::class, 'list'])->name('gwm-incentive.list');
    Route::get('gwm-incentive/create', [GwmIncentiveController::class, 'create'])->name('gwm-incentive.create');
    Route::get('/api/gwm-incentive/sub-models/{model_id}', [GwmIncentiveController::class, 'getSubModels'])->name('gwm-incentive.subModels');
    Route::get('/api/gwm-incentive/check', [GwmIncentiveController::class, 'checkExisting'])->name('gwm-incentive.check');
    Route::get('/api/gwm-incentive/kpi',    [GwmIncentiveController::class, 'getKpi'])->name('gwm-incentive.kpi.get');
    Route::post('/gwm-incentive/kpi',       [GwmIncentiveController::class, 'storeKpi'])->name('gwm-incentive.kpi.store');
    Route::post('/gwm-incentive/upsert-row',[GwmIncentiveController::class, 'upsertRow'])->name('gwm-incentive.upsert-row');

    Route::get('gwm-incentive/report',         [GwmIncentiveController::class, 'report'])->name('gwm-incentive.report');
    Route::get('gwm-incentive/report/export',  [GwmIncentiveController::class, 'exportReport'])->name('gwm-incentive.report.export');

    Route::resource('gwm-incentive', GwmIncentiveController::class)->names([
        'index'   => 'gwm-incentive.index',
        'store'   => 'gwm-incentive.store',
        'edit'    => 'gwm-incentive.edit',
        'update'  => 'gwm-incentive.update',
        'destroy' => 'gwm-incentive.destroy',
    ]);

    // stock-film
    Route::get('stock-film/list', [StockFilmController::class, 'listStock']);
    Route::get('stock-film/report-export', [StockFilmController::class, 'exportReport'])->name('stock-film.report-export');
    Route::post('stock-film/{id}/audit-complete', [StockFilmController::class, 'auditComplete'])->name('stock-film.auditComplete');
    Route::get('stock-film/preview-stock-no', [StockFilmController::class, 'previewStockNo']);
    Route::get('stock-film/{id}/view-more', [StockFilmController::class, 'viewMore'])->name('stock-film.viewMore');
    Route::resource('stock-film', StockFilmController::class)->names([
        'index'   => 'stock-film.index',
        'create'  => 'stock-film.create',
        'store'   => 'stock-film.store',
        'edit'    => 'stock-film.edit',
        'update'  => 'stock-film.update',
        'destroy' => 'stock-film.destroy',
    ]);

    // film-settings
    Route::get('film-settings', [FilmSettingController::class, 'index'])->name('film-settings.index');
    Route::get('film-settings/modal', [FilmSettingController::class, 'modal'])->name('film-settings.modal');
    Route::post('film-settings/global', [FilmSettingController::class, 'updateGlobal'])->name('film-settings.global');
    Route::post('film-settings/costs', [FilmSettingController::class, 'updateCost'])->name('film-settings.costs');

    // film-price-list
    Route::get('film-price-list/list', [FilmPriceListController::class, 'list']);
    Route::get('film-price-list/calculate', [FilmPriceListController::class, 'calculate']);
    Route::get('film-price-list/{modelId}/edit-model', [FilmPriceListController::class, 'editModel'])->name('film-price-list.edit-model');
    Route::post('film-price-list/{modelId}/update-model', [FilmPriceListController::class, 'updateModel'])->name('film-price-list.update-model');
    Route::resource('film-price-list', FilmPriceListController::class)->names([
        'index'   => 'film-price-list.index',
        'create'  => 'film-price-list.create',
        'store'   => 'film-price-list.store',
        'edit'    => 'film-price-list.edit',
        'update'  => 'film-price-list.update',
        'destroy' => 'film-price-list.destroy',
    ]);

    // film-usage
    Route::get('film-usage/list', [FilmUsageController::class, 'list']);
    Route::get('film-usage/report-export', [FilmUsageController::class, 'exportReport'])->name('film-usage.report-export');
    Route::get('film-usage/vin-search', [FilmUsageController::class, 'vinSearch'])->name('film-usage.vinSearch');
    Route::get('film-usage/vin-suggest', [FilmUsageController::class, 'vinSuggest']);
    Route::get('film-usage/price-list-lookup', [FilmUsageController::class, 'priceListLookup']);
    Route::get('film-usage/stock-search', [FilmUsageController::class, 'stockSearch']);
    Route::get('film-usage/{id}/view-more', [FilmUsageController::class, 'viewMore'])->name('film-usage.viewMore');
    Route::resource('film-usage', FilmUsageController::class)->only(['index', 'create', 'store', 'destroy'])->names([
        'index'   => 'film-usage.index',
        'create'  => 'film-usage.create',
        'store'   => 'film-usage.store',
        'destroy' => 'film-usage.destroy',
    ]);

    // forecast
    Route::get('/forecast', [ForecastController::class, 'forecastForm'])
        ->name('car-order.form');

    Route::post('/forecast/calculate', [ForecastController::class, 'forecastCalculate'])
        ->name('forecast.calculate');

    // D/Bar — คำนวณยอดที่ต้องสั่ง (แยก brand+branch)
    Route::get('/dbar', [DbarController::class, 'index'])->name('dbar.index');
    Route::post('/dbar/calculate', [DbarController::class, 'calculate'])->name('dbar.calculate');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // customer
    Route::get('customer/{id}/view-more', [CustomerController::class, 'viewMore'])->name('customer-viewMore');
    Route::get('customer/list', [CustomerController::class, 'listCustomer']);
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::get('/api/thailand/provinces', [CustomerController::class, 'getProvinces']);
    Route::get('/api/thailand/districts', [CustomerController::class, 'getDistricts']);
    Route::get('/api/thailand/tambons', [CustomerController::class, 'getTambons']);

    // car-order
    //get color
    Route::get('/api/car-order/color', [CarOrderController::class, 'getColorBySubModel']);
    //get interior color by model
    Route::get('/api/interior-color', [CarOrderController::class, 'getInteriorColorByModel']);
    //price list car
    Route::get('/api/car-order/pricelist-options', [CarOrderController::class, 'getPricelistOptions']);
    Route::get('/api/car-order/pricelist-data', [CarOrderController::class, 'getPricelistData']);
    
    Route::get('purchase-order/list', [PurchaseOrderController::class, 'listPurchaseOrder']);
    Route::get('purchase-order/sale-options', [PurchaseOrderController::class, 'saleOptions']);
    Route::get('/purchase-order/get-campaign', [PurchaseOrderController::class, 'getCampaign']);
    Route::get('purchase-order/summary/{id}', [PurchaseOrderController::class, 'summaryPurchase'])->name('purchase-order.summary');
    Route::get('purchase-order/booking-pdf/{id}', [PurchaseOrderController::class, 'bookingPdf'])->name('purchase-order.booking-pdf');
    Route::get('/api/purchase-order/sub-model/{model_id}', [PurchaseOrderController::class, 'getSubModelPurchase']);
    Route::get('purchase-order/{id}/preview', [PurchaseOrderController::class, 'preview'])->name('purchase-order.preview');
    Route::get('purchase-order/{id}/proxy/{filename?}', [PurchaseOrderController::class, 'proxyAttachment'])->where('filename', '[^/]+')->name('purchase-order.proxy');
    Route::delete('purchase-order/{id}/attachment', [PurchaseOrderController::class, 'deleteAttachment'])->name('purchase-order.delete-attachment');
    Route::post('purchase-order/{id}/change-buyer', [PurchaseOrderController::class, 'changeBuyer'])->name('purchase-order.change-buyer');
    Route::get('/api/purchase-order/customer-trackings', [PurchaseOrderController::class, 'getCustomerTrackings']);
    Route::get('/api/purchase-order/check-customer-tracking', [PurchaseOrderController::class, 'checkCustomerTracking']);
    Route::get('/api/purchase-order/customer-profile', [PurchaseOrderController::class, 'customerProfile']);
    Route::post('/api/purchase-order/customer-profile', [PurchaseOrderController::class, 'saveCustomerProfile']);
    Route::get('purchase-order/viewPO', [PurchaseOrderController::class, 'viewPO'])->name('purchase-order.viewPO');
    Route::get('purchase-order/list-po', [PurchaseOrderController::class, 'listPO']);
    Route::get('purchase-order/viewBooking', [PurchaseOrderController::class, 'viewBooking'])->name('purchase-order.viewBooking');
    Route::get('purchase-order/list-booking', [PurchaseOrderController::class, 'listBooking']);
    Route::get('purchase-order/history', [PurchaseOrderController::class, 'history'])->name('purchase-order.history');
    Route::get('purchase-order/list-history', [PurchaseOrderController::class, 'listHistory']);

    // ตั้งค่า GP (ราคาทุน / ค่าอุปกรณ์ตกแต่ง / คอมขาย รายคัน) — เฉพาะ admin, audit, account
    Route::get('purchase-order/gp-setting', [PurchaseOrderController::class, 'gpSetting'])->name('purchase-order.gp-setting');
    Route::put('purchase-order/gp-setting/{id}', [PurchaseOrderController::class, 'updateGpSetting'])->name('purchase-order.gp-setting.update');
    Route::get('purchase-order/view-more-history/{id}', [PurchaseOrderController::class, 'viewMoreHistory']);
    Route::post('/purchase-order/{id}/cancel-car-order', [PurchaseOrderController::class, 'cancelCarOrder']);
    Route::post('purchase-order/{id}/change-status', [PurchaseOrderController::class, 'changeStatus'])->name('purchase-order.change-status');
    // ดึงคำขออนุมัติกลับ (เฉพาะ admin — เช็คใน controller) : เคลียร์คำขอ + หมุน token กันลิงก์เมลเดิม
    Route::post('purchase-order/{id}/withdraw-approval', [PurchaseOrderController::class, 'withdrawApproval'])->name('purchase-order.withdrawApproval');
    Route::get('/purchase-order/search', [PurchaseOrderController::class, 'search'])->name('purchase-order.search');
    //commission sale
    Route::get('sale/viewCommission', [PurchaseOrderController::class, 'viewCommission'])->name('purchase-order.viewCommission');
    Route::get('purchase-order/list-Commission', [PurchaseOrderController::class, 'listCommission']);
    Route::get('purchase-order/commission-sale-detail/{saleId}', [PurchaseOrderController::class, 'commissionSaleDetail'])->name('purchase-order.commission-sale-detail');
    Route::post('purchase-order/commission-monthly', [PurchaseOrderController::class, 'saveCommissionMonthly'])->name('purchase-order.commission-monthly.save');
    Route::get('purchase-order/commission-target', [PurchaseOrderController::class, 'getMonthlyTarget'])->name('purchase-order.commission-target.get');
    Route::post('purchase-order/commission-target', [PurchaseOrderController::class, 'saveMonthlyTarget'])->name('purchase-order.commission-target.save');
    // cancellation
    Route::get('purchase-order/cancellation', [CancellationController::class, 'index'])->name('purchase-order.cancellation');
    Route::get('purchase-order/list-cancellation', [CancellationController::class, 'list']);
    Route::get('purchase-order/cancellation-data/{id}', [CancellationController::class, 'getData']);
    Route::put('purchase-order/cancellation/{id}/refund', [CancellationController::class, 'updateRefund']);
    Route::put('purchase-order/cancellation/{id}', [CancellationController::class, 'update']);
    Route::post('purchase-order/cancellation/{id}/withdraw-attachment', [CancellationController::class, 'uploadWithdrawAttachment']);
    Route::delete('purchase-order/cancellation/{id}/withdraw-attachment', [CancellationController::class, 'deleteWithdrawAttachment']);
    Route::post('purchase-order/cancellation/{id}/confirm-withdraw', [CancellationController::class, 'confirmWithdraw']);
    Route::get('purchase-order/cancellation/{id}/proxy/{filename?}', [CancellationController::class, 'proxyFile'])->where('filename', '[^/]+');

    // customer tracking
    // API cascade — สถานที่ตาม sub-source (ต้องให้ sale เรียกได้ตอนเพิ่มการติดตาม)
    Route::get('/api/source/places/{source_id}', [SourceController::class, 'apiPlaces']);
    Route::get('customer-tracking/list', [CustomerTrackingController::class, 'list']);
    Route::get('customer-tracking/filter-options', [CustomerTrackingController::class, 'filterOptions']);
    Route::get('customer-tracking/check-duplicate', [CustomerTrackingController::class, 'checkDuplicate']);
    Route::get('customer-tracking/check-phone', [CustomerTrackingController::class, 'checkPhone']);
    Route::get('customer-tracking/report', [CustomerTrackingController::class, 'report'])->name('customer-tracking.report');
    Route::get('customer-tracking/export-excel', [CustomerTrackingController::class, 'exportExcel'])->name('customer-tracking.exportExcel');
    Route::get('customer-tracking/export-by-date', [CustomerTrackingController::class, 'exportExcelByDate'])->name('customer-tracking.exportByDate');
    Route::get('customer-tracking/export-daily', [CustomerTrackingController::class, 'exportDailyReport'])->name('customer-tracking.exportDaily');
    Route::get('customer-tracking/export-overdue', [CustomerTrackingController::class, 'exportOverdueReport'])->name('customer-tracking.exportOverdue');
    Route::get('customer-tracking/export-overdue-sale', [CustomerTrackingController::class, 'exportOverdueSaleReport'])->name('customer-tracking.exportOverdueSale');
    Route::post('customer-tracking/{id}/detail', [CustomerTrackingController::class, 'addDetail'])->name('customer-tracking.addDetail');
    Route::put('customer-tracking/detail/{detailId}', [CustomerTrackingController::class, 'updateDetail'])->name('customer-tracking.updateDetail');
    Route::post('customer-tracking/detail/{detailId}/continue', [CustomerTrackingController::class, 'continueTracking'])->name('customer-tracking.continueTracking');
    Route::post('customer-tracking/{id}/grade', [CustomerTrackingController::class, 'saveGrade'])->name('customer-tracking.saveGrade');
    Route::post('customer-tracking/{id}/test-drive', [CustomerTrackingController::class, 'saveTestDrive'])->name('customer-tracking.saveTestDrive');
    Route::post('customer-tracking/{id}/cancel', [CustomerTrackingController::class, 'cancelTracking'])->name('customer-tracking.cancel');
    Route::delete('customer-tracking/{id}', [CustomerTrackingController::class, 'destroy'])->name('customer-tracking.destroy');
    Route::post('customer-tracking/quick-store-customer', [CustomerTrackingController::class, 'quickStoreCustomer'])->name('customer-tracking.quickStoreCustomer');

    // service check tracking
    Route::get('service-check-tracking/list', [ServiceCheckTrackingController::class, 'list'])->name('service-check-tracking.list');
    Route::get('service-check-tracking/search-salecar', [ServiceCheckTrackingController::class, 'searchSalecar'])->name('service-check-tracking.searchSalecar');
    Route::post('service-check-tracking/{id}/detail', [ServiceCheckTrackingController::class, 'addDetail'])->name('service-check-tracking.addDetail');
    Route::delete('service-check-tracking/{id}', [ServiceCheckTrackingController::class, 'destroy'])->name('service-check-tracking.destroy');

    // pre-delivery inspection (ลูกค้าสัมพันธ์ - ตรวจรถก่อนส่งมอบ)
    Route::get('pre-delivery-inspection/list', [PreDeliveryInspectionController::class, 'list'])->name('pre-delivery-inspection.list');
    Route::get('pre-delivery-inspection/export', [PreDeliveryInspectionController::class, 'exportExcel'])->name('pre-delivery-inspection.export');
    Route::get('pre-delivery-inspection/{salecarId}/data', [PreDeliveryInspectionController::class, 'getInspection'])->name('pre-delivery-inspection.data');
    Route::post('pre-delivery-inspection/{salecarId}/save', [PreDeliveryInspectionController::class, 'save'])->name('pre-delivery-inspection.save');
    Route::delete('pre-delivery-inspection/{id}/file', [PreDeliveryInspectionController::class, 'deleteFile'])->name('pre-delivery-inspection.deleteFile');
    Route::get('pre-delivery-inspection/{inspectionId}/proxy/{filename?}', [PreDeliveryInspectionController::class, 'proxyFile'])->name('pre-delivery-inspection.proxy')->where('filename', '[^/]+');
    Route::get('pre-delivery-inspection/{salecarId}/view-data', [PreDeliveryInspectionController::class, 'viewData'])->name('pre-delivery-inspection.viewData');
    Route::get('pre-delivery-inspection', [PreDeliveryInspectionController::class, 'index'])->name('pre-delivery-inspection.index');

    // SSI หลังส่งมอบ
    Route::get('ssi', [SsiController::class, 'index'])->name('ssi.index');
    Route::get('ssi/list', [SsiController::class, 'list'])->name('ssi.list');
    Route::get('ssi/export', [SsiController::class, 'exportExcel'])->name('ssi.export');
    Route::get('ssi/{salecarId}/edit', [SsiController::class, 'edit'])->name('ssi.edit');
    Route::post('ssi/{salecarId}/delivery', [SsiController::class, 'saveDeliveryInfo'])->name('ssi.delivery.save');
    Route::post('ssi/{salecarId}/backup-phone', [SsiController::class, 'saveBackupPhone'])->name('ssi.backup-phone.save');
    Route::post('ssi/{salecarId}/contact', [SsiController::class, 'saveContact'])->name('ssi.contact.save');
    Route::delete('ssi/{salecarId}/contact/{contactId}', [SsiController::class, 'deleteContact'])->name('ssi.contact.delete');
    Route::post('ssi/{salecarId}/tab2', [SsiController::class, 'saveTab2'])->name('ssi.tab2.save');
    Route::post('ssi/{salecarId}/complete', [SsiController::class, 'markComplete'])->name('ssi.complete');

    // ขออนุมัติเกินงบล่วงหน้า (ยังไม่เป็นการจอง)
    //  - ดูลิสต์: admin/audit_lead/manager/gm/md + sale/lead_sale (เห็นเฉพาะของตัวเอง)
    //  - สร้างการจอง: admin/audit_lead/manager/gm เท่านั้น
    Route::middleware('role:admin,audit_lead,audit_dp,manager,gm,md,sale,lead_sale')->group(function () {
        Route::get('pre-approval', [PreApprovalController::class, 'index'])->name('pre-approval.index');
        Route::get('pre-approval/list', [PreApprovalController::class, 'list']);
        // ลบ: sale ลบได้เฉพาะของตัวเองที่ยังไม่ส่งขออนุมัติ (ตรวจจริงใน canDelete())
        Route::delete('pre-approval/{id}', [PreApprovalController::class, 'destroy'])->name('pre-approval.destroy');
    });
    Route::post('pre-approval/{id}/convert', [PreApprovalController::class, 'convert'])
        ->middleware('role:admin,audit_lead,audit_dp,manager,gm')
        ->name('pre-approval.convert');

    //all resource
    Route::resource('customer-tracking', CustomerTrackingController::class);
    Route::resource('service-check-tracking', ServiceCheckTrackingController::class);
    Route::resource('customer', CustomerController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
