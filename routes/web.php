<?php

use App\Http\Controllers\accessory\AccessoryController;
use App\Http\Controllers\auth\ForgotController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\campaign\CampaignController;
use App\Http\Controllers\car_order\CarOrderController;
use App\Http\Controllers\color\ColorController;
use App\Http\Controllers\customer\CustomerController;
use App\Http\Controllers\finance\FinanceController;
use App\Http\Controllers\forecast\ForecastController;
use App\Http\Controllers\home\HomeController;
use App\Http\Controllers\model_car\ModelCarController;
use App\Http\Controllers\model_car\SubModelCarController;
use App\Http\Controllers\purchase_order\CancellationController;
use App\Http\Controllers\purchase_order\PurchaseOrderController;
use App\Http\Controllers\vehicle\LicenseController;
use App\Http\Controllers\vehicle\VehicleController;
use App\Http\Controllers\BrandSwitchController;
use App\Http\Controllers\delivery_form\DeliveryFormController;
use App\Http\Controllers\invoice\InvoiceController;
use App\Http\Controllers\pricelist_car\PricelistCarController;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'index'])
    ->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::resource('register', RegisterController::class);
Route::resource('forgot', ForgotController::class);

Route::get('/keep-alive', function () {
    session()->put('last_keep_alive', now());
    return response()->json(['status' => 'ok'])
        ->header('Cache-Control', 'no-store');
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/accessory/search', [PurchaseOrderController::class, 'searchAccessory'])->name('accessory.search');
    Route::post('/switch-brand', [BrandSwitchController::class, 'switch'])->name('brand.switch');
    Route::post('/switch-brand/reset', [BrandSwitchController::class, 'reset'])->name('brand.reset');
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
    //sale car booking report 
    Route::get('purchase-order/view-export-saleBooking', [PurchaseOrderController::class, 'viewExportSaleBooking'])->name('purchase-order.view-export-saleBooking');
    Route::get('/purchase-order/saleBooking-export', [PurchaseOrderController::class, 'exportSaleBooking'])->name('purchase-order.saleBooking-export');
    //report gwm stock
    Route::get('purchase-order/view-export-gwm-stock', [PurchaseOrderController::class, 'viewExportGwmStock'])->name('purchase-order.view-export-gwm-stock');
    Route::get('/purchase-order/gwm-stock-export', [PurchaseOrderController::class, 'gwmStockExport'])->name('purchase-order.gwm-stock-export');
    //delivery report
    Route::get('purchase-order/view-export-monthlyDelivery', [PurchaseOrderController::class, 'viewExportMonthlyDelivery'])->name('purchase-order.view-export-monthlyDelivery');
    Route::get('/purchase-order/monthlyDelivery-export', [PurchaseOrderController::class, 'exportMonthlyDelivery'])->name('purchase-order.monthlyDelivery-export');

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

    //campaign
    Route::get('campaign/list', [CampaignController::class, 'listCampaign']);
    Route::get('campaign/{id}/view-more', [CampaignController::class, 'viewMore'])->name('campaign.viewMore');
    Route::post('/campaign/status-cam', [CampaignController::class, 'statusCam'])->name('campaign.status-cam');
    Route::get('/api/campaign/sub-model/{model_id}', [CampaignController::class, 'getSubModelCam']);
    //name campaign
    Route::get('campaign/appellation', [CampaignController::class, 'viewAppellation'])->name('campaign.appellation');
    Route::get('campaign/appellation/list', [CampaignController::class, 'listAppellation']);
    Route::get('campaign/create-appellation', [CampaignController::class, 'createAppellation']);
    Route::post('campaign/store-appellation', [CampaignController::class, 'storeAppellation'])->name('campaign.storeAppellation');
    Route::get('campaign/edit-appellation/{id}', [CampaignController::class, 'editAppellation'])->name('campaign.editAppellation');
    Route::put('campaign/update-appellation/{id}', [CampaignController::class, 'updateAppellation'])->name('campaign.updateAppellation');
    Route::delete('campaign/destroy-appellation/{id}', [CampaignController::class, 'destroyAppellation'])->name('campaign.destroyAppellation');

    // user
    Route::get('user/list', [UserController::class, 'listUser']);
    Route::get('user/{id}/view-more', [UserController::class, 'viewMore'])->name('user.viewMore');

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

    //car-order
    Route::get('car-order/list', [CarOrderController::class, 'listCarOrder']);
    Route::get('car-order/{id}/view-more', [CarOrderController::class, 'viewMore'])->name('car-order.viewMore');
    Route::get('/api/car-order/sub-model', [CarOrderController::class, 'getSubModelCarOrder']);
    Route::get('/car-order/search', [CarOrderController::class, 'search'])->name('car-order.search');
    //get color
    Route::get('/api/car-order/color', [CarOrderController::class, 'getColorBySubModel']);
    //price list car
    Route::get('/api/car-order/pricelist-options', [CarOrderController::class, 'getPricelistOptions']);
    Route::get('/api/car-order/pricelist-data', [CarOrderController::class, 'getPricelistData']);
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
    Route::get('car-order/edit-approve/{id}', [CarOrderController::class, 'editApprove'])->name('car-order.editApprove');
    Route::put('car-order/update-approve/{id}', [CarOrderController::class, 'updateApprove'])->name('car-order.updateApprove');
    // waiting
    Route::get('car-order/edit-approve-waiting/{id}', [CarOrderController::class, 'editApproveWaiting'])->name('car-order.editApproveWaiting');
    Route::put('car-order/update-approve-waiting/{id}', [CarOrderController::class, 'updateApproveWaiting'])->name('car-order.updateApproveWaiting');
    Route::delete('car-order/destroy-approve/{id}', [CarOrderController::class, 'destroyApprove']);
    //condition select ca model 
    Route::get('/api/car-order/models-by-customer', [CarOrderController::class, 'getModelsByCustomer']);

    //color
    Route::get('color/list', [ColorController::class, 'listColor']);
    Route::get('/api/color/sub-model/{model_id}', [ColorController::class, 'getSubModelColorSub']);

    //license ป้ายแดง
    Route::get('license/list', [LicenseController::class, 'listLicense']);
    Route::get('license/{id}/view-more', [LicenseController::class, 'viewMore'])->name('vehicle.license.viewMore');
    Route::post('/license/approve-finance', [LicenseController::class, 'approveFinance']);

    Route::get('/license/stock-export', [LicenseController::class, 'exportLicStock'])->name('license.stock-export');
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
    Route::resource('user', UserController::class);
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

    // forecast
    Route::get('/forecast', [ForecastController::class, 'forecastForm'])
        ->name('car-order.form');

    Route::post('/forecast/calculate', [ForecastController::class, 'forecastCalculate'])
        ->name('forecast.calculate');
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

    Route::get('purchase-order/list', [PurchaseOrderController::class, 'listPurchaseOrder']);
    Route::get('/purchase-order/get-campaign', [PurchaseOrderController::class, 'getCampaign']);
    Route::get('purchase-order/summary/{id}', [PurchaseOrderController::class, 'summaryPurchase'])->name('purchase-order.summary');
    Route::get('/api/purchase-order/sub-model/{model_id}', [PurchaseOrderController::class, 'getSubModelPurchase']);
    Route::get('purchase-order/{id}/preview', [PurchaseOrderController::class, 'preview'])->name('purchase-order.preview');
    Route::get('purchase-order/viewPO', [PurchaseOrderController::class, 'viewPO'])->name('purchase-order.viewPO');
    Route::get('purchase-order/list-po', [PurchaseOrderController::class, 'listPO']);
    Route::get('purchase-order/viewBooking', [PurchaseOrderController::class, 'viewBooking'])->name('purchase-order.viewBooking');
    Route::get('purchase-order/list-booking', [PurchaseOrderController::class, 'listBooking']);
    Route::get('purchase-order/history', [PurchaseOrderController::class, 'history'])->name('purchase-order.history');
    Route::get('purchase-order/list-history', [PurchaseOrderController::class, 'listHistory']);
    Route::get('purchase-order/view-more-history/{id}', [PurchaseOrderController::class, 'viewMoreHistory']);
    Route::post('/purchase-order/{id}/cancel-car-order', [PurchaseOrderController::class, 'cancelCarOrder']);
    Route::get('/purchase-order/search', [PurchaseOrderController::class, 'search'])->name('purchase-order.search');
    //commission sale
    Route::get('sale/viewCommission', [PurchaseOrderController::class, 'viewCommission'])->name('purchase-order.viewCommission');
    Route::get('purchase-order/list-Commission', [PurchaseOrderController::class, 'listCommission']);
    // cancellation
    Route::get('purchase-order/cancellation', [CancellationController::class, 'index'])->name('purchase-order.cancellation');
    Route::get('purchase-order/list-cancellation', [CancellationController::class, 'list']);
    Route::get('purchase-order/cancellation-data/{id}', [CancellationController::class, 'getData']);
    Route::put('purchase-order/cancellation/{id}/refund', [CancellationController::class, 'updateRefund']);
    Route::put('purchase-order/cancellation/{id}', [CancellationController::class, 'update']);
    Route::post('purchase-order/cancellation/{id}/withdraw-attachment', [CancellationController::class, 'uploadWithdrawAttachment']);
    Route::delete('purchase-order/cancellation/{id}/withdraw-attachment', [CancellationController::class, 'deleteWithdrawAttachment']);
    Route::post('purchase-order/cancellation/{id}/confirm-withdraw', [CancellationController::class, 'confirmWithdraw']);

    //all resource
    Route::resource('customer', CustomerController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
