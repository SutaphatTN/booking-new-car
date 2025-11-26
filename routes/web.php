<?php

use App\Http\Controllers\accessory\AccessoryController;
use App\Http\Controllers\auth\ForgotController;
use App\Http\Controllers\auth\LoginController;
use App\Http\Controllers\auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\campaign\CampaignController;
use App\Http\Controllers\car_order\CarOrderController;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\customer\CustomerController;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\home\HomeController;
use App\Http\Controllers\model_car\ModelCarController;
use App\Http\Controllers\model_car\SubModelCarController;
use App\Http\Controllers\purchase_order\PurchaseOrderController;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Models\Salecar;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return redirect()->route('login.index');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('register', RegisterController::class);
Route::resource('login', LoginController::class);
Route::resource('forgot', ForgotController::class);

// Route::get('/test-delete-booking', function () {
//     $results = Salecar::whereNotNull('BookingDate')
//         ->whereDate('BookingDate', '<=', now()->subDays(5))
//         ->where(function ($query) {
//             $query->whereDoesntHave('remainingPayment')
//                   ->orWhereHas('remainingPayment', function ($sub) {
//                       $sub->whereNull('po_number');
//                   });
//         })
//         ->get();

//     return $results; // จะเห็นว่า Eloquent Collection มี record ไหนบ้าง
// });

Route::group(['middleware' => 'auth'], function () {
    // Main Page Route
    // Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

    // layout
    Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
    Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
    Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
    Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
    Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

    // pages
    Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
    Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
    Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
    Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
    Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

    // authentication
    Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    // Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
    Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

    // cards
    Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

    // User Interface
    Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
    Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
    Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
    Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
    Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
    Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
    Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
    Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
    Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
    Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
    Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
    Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
    Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
    Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
    Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
    Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
    Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
    Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
    Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

    // extended ui
    Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
    Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

    // icons
    Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

    // form elements
    Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
    Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

    // form layouts
    Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
    Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

    // tables
    Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');

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
    Route::get('/api/car-order/sub-model/{model_id}', [CarOrderController::class, 'getSubModelCarOrder']);
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
    
    
    
    //all resource
    Route::resource('customer', CustomerController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::resource('accessory', AccessoryController::class);
    Route::resource('campaign', CampaignController::class);
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

    // Route::get('/customer/input', [CustomerController::class, 'create'])->name('customer-input');
    // Route::post('/customer/customer-update', [CustomerController::class, 'insert'])->name('customer-update');

});
