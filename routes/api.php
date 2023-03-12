<?php

use App\Http\Controllers\Vendor\Api\AdminstarionController;
use App\Http\Controllers\Vendor\Api\AuthController;
use App\Http\Controllers\Vendor\Api\PlansController;
use App\Http\Controllers\Vendor\Api\ProductsController;
use App\Http\Controllers\Vendor\Api\SettingsController;
use App\Http\Controllers\Vendor\Api\UsersController;
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
    Route::post('create-modify-quantities',[WarehouseController::class,'createModifyQuantities']);
    Route::post('modify-quantity-details',[WarehouseController::class,'modifyQuantityDetails']);
    Route::post('update-modify-quantities',[WarehouseController::class,'updateModifyQuantities']);
    Route::post('delete-modify-quantities',[WarehouseController::class,'deleteModifyQuantities']);
    Route::post('execution-modify-quantities',[WarehouseController::class,'executionModifyQuantities']);
    Route::post('add-modify-quantities-stock-material',[WarehouseController::class,'addModifyQuantitiesStockMaterial']);
    ############################# modify qty ##########################################
    Route::post('modify-costs',[WarehouseController::class,'modifyCosts']);
    Route::post('create-modify-cost',[WarehouseController::class,'createModifyCost']);
    Route::post('modify-cost-details',[WarehouseController::class,'modifyCostDetails']);
    Route::post('update-modify-cost',[WarehouseController::class,'updateModifyCost']);
    Route::post('delete-modify-cost',[WarehouseController::class,'deleteModifyCost']);
    Route::post('execution-modify-cost',[WarehouseController::class,'executionModifyCost']);
    Route::post('add-modify-cost-stock-material',[WarehouseController::class,'addModifyCostsStockMaterial']);
    ############################# users ##########################################
    Route::post('users',[UsersController::class,'users']);
    Route::post('create-user',[UsersController::class,'createUser']);
    Route::post('update-user',[UsersController::class,'updateUser']);
    Route::post('add-user-tags',[UsersController::class,'addUserTags']);
    Route::post('delete-user-tags',[UsersController::class,'deleteUserTag']);
    Route::post('user-details',[UsersController::class,'userDetails']);
    Route::post('user-add-address',[UsersController::class,'addUserAddress']);
    Route::post('user-update-address',[UsersController::class,'updateUserAddress']);
    Route::post('user-delete-address',[UsersController::class,'deleteUserAddress']);
    Route::post('user-active-deferred-account',[UsersController::class,'activeDeferredAccount']);
    Route::post('user-active-black-list',[UsersController::class,'activeBlackList']);
    Route::post('user-send-message',[UsersController::class,'userSendEmailMessage']);
    ############################## vendor reasons #########################################
    Route::get('vendor-reasons',[AdminstarionController::class,'vendorReasons']);
    Route::post('create-reason',[AdminstarionController::class,'createReason']);
    Route::post('update-reason',[AdminstarionController::class,'updateReason']);
    ######################## Taxes ########################################################
    Route::get('taxes',[AdminstarionController::class,'taxes']);
    Route::post('add-tax',[AdminstarionController::class,'addTax']);
    Route::post('update-tax',[AdminstarionController::class,'updateTax']);
    Route::post('delete-tax',[AdminstarionController::class,'deleteTax']);
    Route::get('taxes-group',[AdminstarionController::class,'taxesGroup']);
    Route::post('add-tax-group',[AdminstarionController::class,'addTaxGroup']);
    Route::post('delete-tax-group',[AdminstarionController::class,'deleteTaxGroup']);
    ####################### Additions ##################################
    Route::post('additions',[ProductsController::class,'additions']);
    Route::get('generate-addition-code',[ProductsController::class,'generateAdditionCode']);
    Route::post('add-new-addition',[ProductsController::class,'addNewAddition']);
    Route::post('update-addition',[ProductsController::class,'updateAddition']);
    Route::post('delete-addition',[ProductsController::class,'deleteAddition']);
    Route::post('sort-additions',[ProductsController::class,'sortAdditions']);
    ##################### addition options #############################
    Route::post('addition-options',[ProductsController::class,'additionOptions']);
    Route::post('add-addition-options',[ProductsController::class,'addAdditionOptions']);
    Route::post('update-addition-options',[ProductsController::class,'updateAdditionOptions']);
    Route::post('delete-addition-options',[ProductsController::class,'deleteAdditionOptions']);
    Route::get('generate-addition-option-code',[ProductsController::class,'generateAdditionOptionCode']);
    Route::post('addition-options-details',[ProductsController::class,'additionOptionsDetails']);
    Route::post('add-addition-option-stock',[ProductsController::class,'addAdditionOptionStock']);
    Route::post('delete-addition-option-stock',[ProductsController::class,'deleteAdditionOptionStock']);
    Route::post('add-addition-options-special-branch-price',[ProductsController::class,'addAdditionOptionsSpecialBranchPrice']);
    Route::post('add-addition-options-branch-dactive',[ProductsController::class,'addAdditionOptionsBranchDactive']);
    ######################### gift cards ####################
    Route::post('gift-cards',[ProductsController::class,'getGiftCards']);
    Route::get('generate-gift-card-code',[ProductsController::class,'generateGiftCardCode']);
    Route::get('generate-gift-card-number',[ProductsController::class,'genrateGiftCardNumber']);
    Route::post('add-gift-card',[ProductsController::class,'addGiftCard']);
    Route::post('update-gift-card',[ProductsController::class,'updateGiftCard']);
    Route::post('delete-gift-card',[ProductsController::class,'deleteGiftCard']);
    Route::post('gift-card-details',[ProductsController::class,'giftCardDetails']);
    Route::post('add-gift-card-tags',[ProductsController::class,'addGiftCardTags']);
    Route::post('delete-gift-card-tag',[ProductsController::class,'deleteGiftCardTag']);
    Route::post('add-gift-card-branch',[ProductsController::class,'addGiftCardBranch']);
    ##################### products #############################
    Route::post('products',[ProductsController::class,'products']);
    Route::get('generate-product-code',[ProductsController::class,'generateProductCode']);
    Route::post('create-product',[ProductsController::class,'createProduct']);
    Route::post('update-product',[ProductsController::class,'updateProduct']);
    Route::post('delete-product',[ProductsController::class,'deleteProduct']);
    Route::post('product-details',[ProductsController::class,'productDetails']);
    Route::post('add-product-tags',[ProductsController::class,'addProductTags']);
    Route::post('delete-product-tag',[ProductsController::class,'deleteProductTag']);
    Route::post('add-product-additions',[ProductsController::class,'addProductAdditions']);
    Route::post('update-product-addition',[ProductsController::class,'updateProductAdditions']);
    Route::post('delete-product-addition',[ProductsController::class,'deleteProductAdditions']);
    Route::post('add-stock-components',[ProductsController::class,'addStockComponents']);
    Route::post('update-stock-components',[ProductsController::class,'updateStockComponents']);
    Route::post('delete-stock-components',[ProductsController::class,'deleteStockComponents']);
    Route::post('add-product-branch-price',[ProductsController::class,'addProductSpecialBranchPrice']);
    Route::post('delete-product-branch-price',[ProductsController::class,'deleteProductSpecialBranchPrice']);
    Route::post('add-product-branch-dactive',[ProductsController::class,'addProductSpecialBranchDactive']);
    Route::post('add-product-traits',[ProductsController::class,'addProductTraits']);
    Route::post('delete-product-trait',[ProductsController::class,'deleteProductTrait']);
    ############################ product colections #########################
    Route::post('product-collections',[ProductsController::class,'productCollections']);
    Route::get('generate-product-collection-code',[ProductsController::class,'generateProductCollectionCode']);
    Route::post('create-product-collection',[ProductsController::class,'createProductCollection']);
    Route::post('update-product-collection',[ProductsController::class,'updateProductCollection']);
    Route::post('product-collection-details',[ProductsController::class,'productCollectionDetails']);
    Route::post('delete-product-collection',[ProductsController::class,'deleteProductCollection']);
    Route::post('add-product-collection-tags',[ProductsController::class,'addProductCollectionTags']);
    Route::post('delete-product-collection-tag',[ProductsController::class,'deleteProductCollectionTag']);
    Route::post('add-product-collection-branch-price',[ProductsController::class,'addProductCollectionBranchPrice']);
    Route::post('delete-product-collection-branch-price',[ProductsController::class,'deleteProductCollectionBranchPrice']);
    Route::post('add-product-collection-branch-dactive',[ProductsController::class,'addProductCollectionBranchDactive']);
    Route::post('add-product-collection-products',[ProductsController::class,'addProductCollectionProducts']);
    Route::post('delete-product-collection-products',[ProductsController::class,'deleteProductCollectionProducts']);

});
