<?php

use App\Http\Controllers\Vendor\Api\AdminstarionController;
use App\Http\Controllers\Vendor\Api\AuthController;
use App\Http\Controllers\Vendor\Api\PlansController;
use App\Http\Controllers\Vendor\Api\ProductsController;
use App\Http\Controllers\Vendor\Api\SettingsController;
use App\Http\Controllers\Vendor\Api\VendorController;
use App\Http\Controllers\Vendor\Api\WarehouseController;
use Illuminate\Support\Facades\Route;
use Spatie\SimpleExcel\SimpleExcelWriter;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'vendor/v1'],function (){
   Route::get('countries',[SettingsController::class,'countries']);
   Route::get('currencies',[SettingsController::class,'currencies']);
   Route::get('activities-types',[SettingsController::class,'activetiesTypes']);
   Route::post('country-cities',[SettingsController::class,'countryCities']);
   ######################### auth ################################
   Route::post('register',[AuthController::class,'register']);
   Route::post('login',[AuthController::class,'login']);
   Route::post('forget-password',[AuthController::class,'forgetPassword']);

});
Route::group(['middleware' => 'JwtMiddleware','prefix' => 'vendor/v1'],function (){
    ################### products #######################
    Route::post('all-categories',[ProductsController::class,'allCategories']);
    Route::post('trashed-categories',[ProductsController::class,'trashedCategories']);
    Route::post('category',[ProductsController::class,'categories']);
    Route::post('create-category',[ProductsController::class,'createCategory']);
    Route::post('update-category',[ProductsController::class,'updateCategory']);
    Route::post('delete-category',[ProductsController::class,'deleteCategory']);
    Route::post('sort-categories',[ProductsController::class,'sortCategories']);
    Route::get('generate-category-code',[ProductsController::class,'generateCategoryCode']);
    ######################### auth ################################
    Route::post('verify-account',[AuthController::class,'verifyAccount']);
    Route::post('resend-active-code',[AuthController::class,'resendActiveCode']);
    Route::post('logout',[AuthController::class,'logout']);
    ######################## bouquets ##################3
    Route::get('bouquets',[PlansController::class,'bouquets']);
    Route::post('bouquet-details',[PlansController::class,'bouquetDetails']);
    Route::post('subscribe-bouquet',[PlansController::class,'subscribePlan']);
    Route::post('myfatorah-payment',[PlansController::class,'myfatorah_payment']);
    ######################### vendor data ###################
    Route::get('vendor-data',[VendorController::class,'vendorData']);

    ############################# stock categories #######################
    Route::post('stock-categories',[WarehouseController::class,'stockCategories']);
    Route::post('stock-trashed-categories',[WarehouseController::class,'stockTrashedCategories']);
    Route::post('add-stock-categories',[WarehouseController::class,'addStokCategory']);
    Route::post('delete-stock-categories',[WarehouseController::class,'deleteStokCategory']);
    Route::get('generate-stock-category-code',[WarehouseController::class,'generateStokCategoryCode']);
    ###################### stock ########################3
    Route::post('all-stocks',[WarehouseController::class,'allStoks']);
    Route::post('stoks-material',[WarehouseController::class,'stoksMaterieal']);
    Route::post('trashed-stocks',[WarehouseController::class,'trashedStoks']);
    Route::post('create-stock',[WarehouseController::class,'createStock']);
    Route::post('stock-details',[WarehouseController::class,'stockDetails']);
    Route::post('delete-stock',[WarehouseController::class,'deleteStock']);
    Route::post('add-tag-stock',[WarehouseController::class,'addTagStock']);
    Route::post('delete-tag-stock',[WarehouseController::class,'deleteTagStock']);
    Route::get('generate-stock-material-code',[WarehouseController::class,'generateStoksMateriealCode']);
    ######################## suppliers ####################
    Route::post('suppliers',[WarehouseController::class,'suppliers']);
    Route::post('trashed-suppliers',[WarehouseController::class,'trashedSuppliers']);
    Route::post('create-supplier',[WarehouseController::class,'createSupplier']);
    Route::post('update-supplier',[WarehouseController::class,'updateSupplier']);
    Route::post('supplier-details',[WarehouseController::class,'supplierDetails']);
    Route::post('delete-supplier',[WarehouseController::class,'deleteSupplier']);
    Route::post('add-supplier-tag',[WarehouseController::class,'addSupplierTag']);
    Route::post('delete-supplier-tag',[WarehouseController::class,'deleteSupplierTag']);
    Route::post('add-supplier-stock',[WarehouseController::class,'addSupplierStock']);
    Route::post('delete-supplier-stock',[WarehouseController::class,'deleteSupplierStock']);
    Route::get('generate-supplier-code',[WarehouseController::class,'generateSupplierCode']);
    ############# store house ###########
    Route::post('store-house',[WarehouseController::class,'storeHouse']);
    Route::post('active-store-house',[WarehouseController::class,'activeStoreHouse']);
    Route::post('inactive-store-house',[WarehouseController::class,'deactiveStoreHouse']);
    Route::post('add-store-house-stock-categories',[WarehouseController::class,'addStoreHouse']);
    Route::post('delete-store-house-stock-category',[WarehouseController::class,'deleteStoreHouse']);
    Route::get('generate-store-house-code',[WarehouseController::class,'generateStoreHouseCode']);
    #################### tags ################3
    Route::get('tags',[WarehouseController::class,'tagsList']);
    Route::post('create-tag',[WarehouseController::class,'createTag']);
    Route::post('update-tag',[WarehouseController::class,'updateTag']);
    Route::post('delete-tag',[WarehouseController::class,'deleteTag']);
    Route::get('generate-tag-code',[WarehouseController::class,'generateTagsCode']);
    #################### delivery areas ################3
    Route::post('delivery-areas',[VendorController::class,'deliveryAreas']);
    Route::post('trashed-delivery-areas',[VendorController::class,'treashedDeliveryAreas']);
    Route::post('create-delivery-area',[VendorController::class,'createDeliveryArea']);
    Route::post('update-delivery-area',[VendorController::class,'updateDeliveryArea']);
    Route::post('delete-delivery-area',[VendorController::class,'deleteDeliveryArea']);
    ######################## branches ###################################
    Route::post('branches',[AdminstarionController::class,'branches']);
    Route::post('create-branch',[AdminstarionController::class,'createBranch']);
    Route::post('update-branch',[AdminstarionController::class,'updateBranch']);
    Route::post('delete-branch',[AdminstarionController::class,'deleteBranch']);
    Route::post('branch-details',[AdminstarionController::class,'branchDetails']);
    Route::post('add-branch-tag',[AdminstarionController::class,'addBranchTag']);
    Route::post('delete-branch-tag',[AdminstarionController::class,'deleteBranchTag']);
    Route::post('add-branch-delivery-area',[AdminstarionController::class,'addBranchDeliveryArea']);
    Route::post('delete-branch-delivery-area',[AdminstarionController::class,'deleteBranchDeliveryArea']);
    Route::get('generate-branch-code',[AdminstarionController::class,'generateBranchCode']);
    ######################## permissions ###################################
    Route::get('permissions-list',[AdminstarionController::class,'permitionsList']);
    ###################### employees #########################################
    Route::post('employees',[AdminstarionController::class,'employees']);
    Route::post('employee-details',[AdminstarionController::class,'employeeDetails']);
    Route::post('trashed-employees',[AdminstarionController::class,'trashedEmployees']);
    Route::post('employees-access-apps',[AdminstarionController::class,'employeesAccessApps']);
    Route::post('employees-access-admin-panel',[AdminstarionController::class,'employeesAccessAdminPanel']);
    Route::post('add-employee',[AdminstarionController::class,'addEmployee']);
    Route::post('edit-employee',[AdminstarionController::class,'editEmployee']);
    Route::post('delete-employee',[AdminstarionController::class,'deleteEmployee']);
    Route::post('change-employee-status',[AdminstarionController::class,'changeEmployeeStatus']);
    Route::post('change-employee-password',[AdminstarionController::class,'changeEmployeePassword']);
    Route::post('add-employee-tag',[AdminstarionController::class,'addEmployeeTag']);
    Route::post('delete-employee-tag',[AdminstarionController::class,'deleteEmployeeTag']);
    Route::post('add-employee-branch',[AdminstarionController::class,'addEmploeeBranche']);
    Route::post('delete-employee-branch',[AdminstarionController::class,'deleteEmploeeBranche']);
    ###################### rools ###############
    Route::get('get-rools',[AdminstarionController::class,'getRools']);
    Route::post('create-rools',[AdminstarionController::class,'createRools']);
    Route::post('rool-details',[AdminstarionController::class,'roolDetails']);
    Route::post('update-rools',[AdminstarionController::class,'updateRools']);
    Route::post('delete-rools',[AdminstarionController::class,'deleteRools']);
    ################# inventoray template ################
    Route::post('stock-invintory-template',[WarehouseController::class,'getInvintoryTemplate']);
    Route::get('stock-generate-code-invintory-template',[WarehouseController::class,'generateInvintoryTemplateCode']);
    Route::post('stock-add-invintory-template',[WarehouseController::class,'addStockInventoryTemplate']);
    Route::post('stock-edit-invintory-template',[WarehouseController::class,'editStockInventoryTemplate']);
    Route::post('stock-delete-inventory-template',[WarehouseController::class,'deleteStockInventoryTemplate']);
    ######################## stock purchases orders ##########################
    Route::get('generate-stock-purchase-orders-code',[WarehouseController::class,'generateStokPurchaseOrdersCode']);
    Route::post('stock-purchase-orders',[WarehouseController::class,'stockPurchaseOrders']);
    Route::post('stock-purchase-warehouse-orders',[WarehouseController::class,'stockPurchaseWarehouseOrders']);
    Route::post('create-stock-purchase-orders',[WarehouseController::class,'createStockPurchaseOrders']);
    Route::post('update-stock-purchase-orders',[WarehouseController::class,'updateStockPurchaseOrders']);
    Route::post('stock-purchase-order-details',[WarehouseController::class,'StockPurchaseOrderDetails']);
    Route::get('generate-stok-purchase-code',[WarehouseController::class,'generateStokPurchaseCode']);
    Route::post('stock-purchase-order-change-status-sent',[WarehouseController::class,'StockPurchaseOrderChangeStatusSent']);
    Route::post('stock-purchase-order-change-status-cancel',[WarehouseController::class,'StockPurchaseOrderChangeStatusCanel']);
    Route::post('stock-purchase-order-change-status-close',[WarehouseController::class,'StockPurchaseOrderChangeStatusClose']);
    Route::post('add-purchase-order-material',[WarehouseController::class,'addStockPurchaseOrderStockMaterial']);
    Route::post('update-purchase-order-material-qty',[WarehouseController::class,'updatePurchaseOrderStockMaterialQty']);
    #################################### productions #####################################
    Route::post('stock-productions',[WarehouseController::class,'stockProductions']);
    Route::post('create-stock-production',[WarehouseController::class,'createStockProduction']);
    Route::post('update-stock-production',[WarehouseController::class,'updateStockProduction']);
    Route::post('stock-production-update-status',[WarehouseController::class,'stockProductionUpdateStatus']);
    Route::post('stock-production-details',[WarehouseController::class,'stockProductionDetails']);
    Route::post('production-add-stock-material',[WarehouseController::class,'productionAddStockMaterial']);
    ############################# modify qty ##########################################
    Route::post('modify-quantities',[WarehouseController::class,'modifyQuantities']);
    Route::post('createModify-quantities',[WarehouseController::class,'createModifyQuantities']);
    Route::post('modify-quantityDetails',[WarehouseController::class,'modifyQuantityDetails']);
    Route::post('update-modify-quantities',[WarehouseController::class,'updateModifyQuantities']);
    Route::post('execution-modify-quantities',[WarehouseController::class,'executionModifyQuantities']);
    Route::post('add-modify-quantities-stock-material',[WarehouseController::class,'addModifyQuantitiesStockMaterial']);
    ############################## vendor reasons #########################################
    Route::get('vendor-reasons',[AdminstarionController::class,'vendorReasons']);
    Route::post('create-reason',[AdminstarionController::class,'createReason']);
    Route::post('update-reason',[AdminstarionController::class,'updateReason']);

});
