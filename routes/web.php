<?php

use App\Http\Controllers\accessory\AccessoryController;
use App\Http\Controllers\auth\ForgotController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use App\Http\Controllers\auth\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\campaign\CampaignController;
use App\Http\Controllers\car_order\CarOrderController;
use App\Http\Controllers\customer\CustomerController;
use App\Http\Controllers\finance\FinanceController;
use App\Http\Controllers\home\HomeController;
use App\Http\Controllers\model_car\ModelCarController;
use App\Http\Controllers\model_car\SubModelCarController;
use App\Http\Controllers\purchase_order\PurchaseOrderController;

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

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // customer
    Route::get('customer/{id}/view-more', [CustomerController::class, 'viewMore'])->name('customer-viewMore');
    Route::get('customer/list', [CustomerController::class, 'listCustomer']); //customer table
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search'); //customer search

    //purchase order -> search accessory, list purchase for view, get campaign, view more, pdf
    Route::get('/accessory/search', [PurchaseOrderController::class, 'searchAccessory'])->name('accessory.search');
    Route::get('purchase-order/list', [PurchaseOrderController::class, 'listPurchaseOrder']);
    Route::get('/purchase-order/get-campaign', [PurchaseOrderController::class, 'getCampaign']);
    Route::get('purchase-order/{id}/view-more', [PurchaseOrderController::class, 'viewMore'])->name('purchase-order-viewMore');
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
    Route::get('purchase-order/viewFN', [FinanceController::class, 'viewFN'])->name('purchase-order.viewFN');
    Route::get('purchase-order/{id}/view-more', [FinanceController::class, 'viewMoreFN'])->name('purchase-order.viewMoreFN');
    Route::get('purchase-order/list-fn', [FinanceController::class, 'listFN']);
    Route::get('purchase-order/edit-fn/{id}', [FinanceController::class, 'editFN'])->name('purchase-order.editFN');
    Route::put('purchase-order/update-fn/{id}', [FinanceController::class, 'updateFN'])->name('purchase-order.updateFN');
    Route::delete('purchase-order/destroy-fn/{id}', [FinanceController::class, 'destroyFN'])->name('purchase-order.destroyFN');
    Route::get('/purchase-order/booking-export', [PurchaseOrderController::class, 'exportBooking'])->name('purchase-order.booking-export');
    Route::post('/purchase-order/{id}/cancel-car-order', [PurchaseOrderController::class, 'cancelCarOrder']);
    Route::get('/purchase-order/search', [PurchaseOrderController::class, 'search'])->name('purchase-order.search');

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

    //car
    Route::get('model-car/list', [ModelCarController::class, 'listCar']);
    Route::get('sub-model-car/list', [SubModelCarController::class, 'listSubCar']);
    Route::get('sub-model-car/{id}/view-more', [SubModelCarController::class, 'viewMore'])->name('sub-model-car.viewMore');
    Route::post('/sub-model-car/status-sub-car', [SubModelCarController::class, 'statusSubCar'])->name('sub-model-car.status-sub-car');

    //logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
    Route::delete('car-order/destroy-approve/{id}', [CarOrderController::class, 'destroyApprove']);
    //condition select ca model 
    Route::get('/api/car-order/models-by-customer', [CarOrderController::class, 'getModelsByCustomer']);

    //all resource
    Route::resource('customer', CustomerController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::resource('accessory', AccessoryController::class);
    Route::resource('campaign', CampaignController::class);
    Route::resource('user', UserController::class);
    Route::resource('finance', FinanceController::class);
    Route::resource('car-order', CarOrderController::class);

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
});

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});