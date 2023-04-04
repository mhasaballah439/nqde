<?php

use App\Http\Controllers\Vendor\Api\AdminstarionController;
use App\Http\Controllers\Vendor\Api\AuthController;
use App\Http\Controllers\Vendor\Api\OrdersController;
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
    Route::post('add-categories-products',[ProductsController::class,'addCategoriesProducts']);
    Route::post('add-categories-gift-card',[ProductsController::class,'addCategoriesGiftCard']);
    Route::post('categories-delete-list',[ProductsController::class,'categoriesDeleteList']);
    Route::post('categories-restore-list',[ProductsController::class,'categoriesRestoreList']);
    Route::post('category-restore-single-item',[ProductsController::class,'categoryRestoreSingleItem']);
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
    Route::post('stock-category-details',[WarehouseController::class,'stockCategoryDetails']);
    Route::post('delete-stock-categories',[WarehouseController::class,'deleteStokCategory']);
    Route::get('generate-stock-category-code',[WarehouseController::class,'generateStokCategoryCode']);
    Route::post('stock-category-add-stocks',[WarehouseController::class,'stockCategoryAddStocks']);
    Route::post('stock-category-delete-stocks',[WarehouseController::class,'stockCategoryDeleteStocks']);
    Route::post('stock-category-delete-list',[WarehouseController::class,'stockCategoryDeleteList']);
    Route::post('stock-category-restore-list',[WarehouseController::class,'stockCategoryRestoreList']);
    Route::post('stock-category-restore-single-item',[WarehouseController::class,'stockCategoryRestoreSingleItem']);
    ###################### stock ########################3
    Route::post('all-stocks',[WarehouseController::class,'allStoks']);
    Route::post('stoks-material',[WarehouseController::class,'stoksMaterieal']);
    Route::post('trashed-stocks',[WarehouseController::class,'trashedStoks']);
    Route::post('create-stock',[WarehouseController::class,'createStock']);
    Route::post('stock-details',[WarehouseController::class,'stockDetails']);
    Route::post('delete-stock',[WarehouseController::class,'deleteStock']);
    Route::post('add-tag-stock',[WarehouseController::class,'addTagStock']);
    Route::post('delete-tag-stock',[WarehouseController::class,'deleteTagStock']);
    Route::post('stocks-add-tags',[WarehouseController::class,'stocksAddTags']);
    Route::post('stocks-delete-tags',[WarehouseController::class,'stocksDeleteTags']);
    Route::post('stocks-list-delete',[WarehouseController::class,'stocksListDelete']);
    Route::post('stocks-restore-deleted',[WarehouseController::class,'stocksRestoreDeleted']);
    Route::post('stocks-restore-single-deleted',[WarehouseController::class,'stocksRestoreSingleDeleted']);
    Route::post('stocks-add-suppliers',[WarehouseController::class,'stocksAddSuppliers']);
    Route::post('stocks-delete-suppliers',[WarehouseController::class,'stocksDeleteSuppliers']);
    Route::post('stocks-add-storehouse',[WarehouseController::class,'stocksAddStorehouse']);
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
    Route::post('supplier-add-tags',[WarehouseController::class,'supplierAddTags']);
    Route::post('supplier-delete-tags',[WarehouseController::class,'supplierDeleteTags']);
    Route::post('supplier-list-delete',[WarehouseController::class,'supplierListDelete']);
    Route::post('supplier-restore-deleted',[WarehouseController::class,'supplierRestoreDeleted']);
    Route::post('supplier-restore-single-deleted',[WarehouseController::class,'supplierRestoreSingleDeleted']);
    Route::post('add-suppliers-stocks',[WarehouseController::class,'addSuppliersStocks']);
    Route::post('delete-suppliers-stocks',[WarehouseController::class,'deleteSuppliersStocks']);
    Route::get('generate-supplier-code',[WarehouseController::class,'generateSupplierCode']);
    ############# store house ###########
    Route::post('store-house',[WarehouseController::class,'storeHouse']);
    Route::post('active-store-house',[WarehouseController::class,'activeStoreHouse']);
    Route::post('inactive-store-house',[WarehouseController::class,'deactiveStoreHouse']);
    Route::post('add-store-house',[WarehouseController::class,'addStoreHouse']);
    Route::post('delete-store-house-stock-category',[WarehouseController::class,'deleteStoreHouse']);
    Route::get('generate-store-house-code',[WarehouseController::class,'generateStoreHouseCode']);
    Route::post('store-house-add-branches',[WarehouseController::class,'storeHouseAddBranches']);
    Route::post('store-house-add-active',[WarehouseController::class,'storeHouseAddActive']);
    Route::post('store-house-delete-list',[WarehouseController::class,'storeHouseDeleteList']);
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
    Route::post('delivery-areas-delete-list',[VendorController::class,'deliveryAreasDeleteList']);
    Route::post('delivery-areas-restore-list',[VendorController::class,'deliveryAreasRestoreList']);
    Route::post('delivery-areas-restore-single-item',[VendorController::class,'deliveryAreasRestoreSingleItem']);
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
    Route::post('branches-add-tags',[AdminstarionController::class,'branchesAddTags']);
    Route::post('branches-delete-tags',[AdminstarionController::class,'branchesDeleteTags']);
    Route::post('branches-delete-list',[AdminstarionController::class,'branchesDeleteList']);
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
    Route::post('employee-add-tags',[AdminstarionController::class,'employeeAddTags']);
    Route::post('employee-delete-tags',[AdminstarionController::class,'employeeDeleteTags']);
    Route::post('employee-delete-list',[AdminstarionController::class,'employeeDeleteList']);
    Route::post('employee-restore-list',[AdminstarionController::class,'employeeRestoreList']);
    Route::post('employee-restore-single-item',[AdminstarionController::class,'employeeRestoreSingleItem']);
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
    Route::post('add-purchase-order-stock-material',[WarehouseController::class,'addStockPurchaseOrderStockMaterial']);
    Route::post('update-purchase-order-material-qty',[WarehouseController::class,'updatePurchaseOrderStockMaterialQty']);
    Route::post('stock-purchase-orders-delete-list',[WarehouseController::class,'stockPurchaseOrderDeleteList']);
    #################################### productions #####################################
    Route::post('stock-productions',[WarehouseController::class,'stockProductions']);
    Route::post('create-stock-production',[WarehouseController::class,'createStockProduction']);
    Route::post('update-stock-production',[WarehouseController::class,'updateStockProduction']);
    Route::post('stock-production-update-status',[WarehouseController::class,'stockProductionUpdateStatus']);
    Route::post('stock-production-details',[WarehouseController::class,'stockProductionDetails']);
    Route::post('production-add-stock-material',[WarehouseController::class,'productionAddStockMaterial']);
    Route::post('productions-delete-list',[WarehouseController::class,'productionsDeleteList']);
    ############################# modify qty ##########################################
    Route::post('modify-quantities',[WarehouseController::class,'modifyQuantities']);
    Route::post('create-modify-quantities',[WarehouseController::class,'createModifyQuantities']);
    Route::post('modify-quantity-details',[WarehouseController::class,'modifyQuantityDetails']);
    Route::post('update-modify-quantities',[WarehouseController::class,'updateModifyQuantities']);
    Route::post('delete-modify-quantities',[WarehouseController::class,'deleteModifyQuantities']);
    Route::post('execution-modify-quantities',[WarehouseController::class,'executionModifyQuantities']);
    Route::post('add-modify-quantities-stock-material',[WarehouseController::class,'addModifyQuantitiesStockMaterial']);
    Route::post('modify-quantities-delete-list',[WarehouseController::class,'modifyQuantitiesDeleteList']);
    ############################# modify qty ##########################################
    Route::post('modify-costs',[WarehouseController::class,'modifyCosts']);
    Route::post('create-modify-cost',[WarehouseController::class,'createModifyCost']);
    Route::post('modify-cost-details',[WarehouseController::class,'modifyCostDetails']);
    Route::post('update-modify-cost',[WarehouseController::class,'updateModifyCost']);
    Route::post('delete-modify-cost',[WarehouseController::class,'deleteModifyCost']);
    Route::post('execution-modify-cost',[WarehouseController::class,'executionModifyCost']);
    Route::post('add-modify-cost-stock-material',[WarehouseController::class,'addModifyCostsStockMaterial']);
    Route::post('modify-cost-delete-list',[WarehouseController::class,'modifyCostDeleteList']);
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
    Route::post('users-add-tags',[UsersController::class,'usersAddTags']);
    Route::post('users-delete-tags',[UsersController::class,'usersDeleteTags']);
    Route::post('users-delete-accounts',[UsersController::class,'usersDeleteAccounts']);
    Route::post('users-active-accounts',[UsersController::class,'usersActiveAccounts']);
    Route::post('users-blacklist-accounts',[UsersController::class,'usersBlackListAccounts']);
    Route::post('users-deferred-accounts',[UsersController::class,'usersDeferredAccounts']);
    Route::post('users-restore-accounts',[UsersController::class,'usersRestoreAccounts']);
    Route::post('users-restore-single-accounts',[UsersController::class,'usersRestoreSingleAccounts']);
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
    Route::post('taxes-delete-list',[AdminstarionController::class,'taxesDeleteList']);
    ####################### Additions ##################################
    Route::post('additions',[ProductsController::class,'additions']);
    Route::get('generate-addition-code',[ProductsController::class,'generateAdditionCode']);
    Route::post('add-new-addition',[ProductsController::class,'addNewAddition']);
    Route::post('update-addition',[ProductsController::class,'updateAddition']);
    Route::post('delete-addition',[ProductsController::class,'deleteAddition']);
    Route::post('sort-additions',[ProductsController::class,'sortAdditions']);
    Route::post('additions-add-products',[ProductsController::class,'additionsAddProducts']);
    Route::post('additions-delete-products',[ProductsController::class,'additionsDeleteProducts']);
    Route::post('additions-delete-list',[ProductsController::class,'additionsDeleteList']);
    Route::post('additions-restore-list',[ProductsController::class,'additionsRestoreList']);
    Route::post('additions-restore-single-item',[ProductsController::class,'additionsRestoreSingleItem']);
    Route::post('additions-add-addition-options',[ProductsController::class,'additionsAddAdditionOptions']);
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
    Route::post('additions-options-add-additions',[ProductsController::class,'additionsOptionsAddAdditions']);
    Route::post('additions-options-add-tax-group',[ProductsController::class,'additionsOptionsAddTaxGroup']);
    Route::post('additions-options-deactive-branches',[ProductsController::class,'additionsOptionsDeactiveBranches']);
    Route::post('additions-options-add-stocks',[ProductsController::class,'additionsOptionsAddStocks']);
    Route::post('additions-options-delete-stocks',[ProductsController::class,'additionsOptionsDeleteStocks']);
    Route::post('additions-options-delete-list',[ProductsController::class,'additionsOptionsDeleteList']);
    Route::post('additions-options-active',[ProductsController::class,'additionsOptionsActive']);
    Route::post('additions-options-restore-list',[ProductsController::class,'additionsOptionsRestoreList']);
    Route::post('additions-options-restore-single-item',[ProductsController::class,'additionsOptionsRestoreSingleItem']);
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
    Route::post('gift-card-delete-list',[ProductsController::class,'giftCardDeleteList']);
    Route::post('gift-card-restore-list',[ProductsController::class,'giftCardRestoreList']);
    Route::post('gift-card-restore-single-item',[ProductsController::class,'giftCardRestoreSingleItem']);
    Route::post('gift-card-add-tags',[ProductsController::class,'giftCardAddTags']);
    Route::post('gift-card-delete-tags',[ProductsController::class,'giftCardDeleteTags']);
    Route::post('gift-card-active-list',[ProductsController::class,'giftCardActiveList']);
    Route::post('gift-card-deactive-branches',[ProductsController::class,'giftCardDeactiveBranches']);
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
    Route::post('add-products-category',[ProductsController::class,'addProductsCategory']);
    Route::post('products-delete-list',[ProductsController::class,'productsDeleteList']);
    Route::post('products-restore-list',[ProductsController::class,'productsRestoreList']);
    Route::post('product-restore-single-item',[ProductsController::class,'productRestoreSingleItem']);
    Route::post('products-add-tags',[ProductsController::class,'productsAddTags']);
    Route::post('products-delete-tags',[ProductsController::class,'productsDeleteTags']);
    Route::post('products-add-collections',[ProductsController::class,'productsAddCollections']);
    Route::post('products-delete-collections',[ProductsController::class,'productsDeleteCollections']);
    Route::post('products-add-tax-group',[ProductsController::class,'productsAddTaxGroup']);
    Route::post('products-add-additioins',[ProductsController::class,'productsAddAdditioins']);
    Route::post('products-delete-additioins',[ProductsController::class,'productsDeleteAdditioins']);
    Route::post('products-add-temporary-events',[ProductsController::class,'productsAddTemporaryEvents']);
    Route::post('products-delete-temporary-events',[ProductsController::class,'productsDeleteTemporaryEvents']);
    Route::post('products-add-deactive-branches',[ProductsController::class,'productsAddDeactiveBranches']);
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
    Route::post('product-collection-add-products',[ProductsController::class,'productCollectionAddProducts']);
    Route::post('product-collection-delete-products',[ProductsController::class,'productCollectionDeleteProducts']);
    Route::post('product-collection-delete-list',[ProductsController::class,'productCollectionDeleteList']);
    Route::post('product-collection-restore-list',[ProductsController::class,'productCollectionRestoreList']);
    Route::post('product-collection-restore-single-item',[ProductsController::class,'productCollectionRestoreSingleItem']);
    Route::post('product-collection-add-tags',[ProductsController::class,'productCollectionAddTags']);
    Route::post('product-collection-delete-tags',[ProductsController::class,'productCollectionDeleteTags']);
    Route::post('product-collection-tax-group',[ProductsController::class,'productCollectionTaxGroup']);
    Route::post('product-collection-add-temporary-events',[ProductsController::class,'productCollectionAddTemporaryEvents']);
    Route::post('product-collection-delete-temporary-events',[ProductsController::class,'productCollectionDeleteTemporaryEvents']);
    Route::post('product-collection-active',[ProductsController::class,'productCollectionActive']);
    ################################## store transfare ############################
    Route::post('stock-transfare',[WarehouseController::class,'stockTransfare']);
    Route::get('generate-stock-transfare-code',[WarehouseController::class,'generateStockTransfareCode']);
    Route::post('create-stock-transfare',[WarehouseController::class,'createStockTransfare']);
    Route::post('update-stock-transfare',[WarehouseController::class,'updateStockTransfare']);
    Route::post('delete-stock-transfare',[WarehouseController::class,'deleteStockTransfare']);
    Route::post('add-stock-transfare-stocks',[WarehouseController::class,'addStockTransfareStocks']);
    Route::post('update-store-transfare-stock',[WarehouseController::class,'updateStoreTransfareStock']);
    Route::post('store-transfare-details',[WarehouseController::class,'storeTransfareDetails']);
    Route::post('stock-transfare-delete-list',[WarehouseController::class,'storeTransfareDeleteList']);
    ############################# stock check ####################
    Route::post('stock-checks',[WarehouseController::class,'stockChecks']);
    Route::get('generate-stock-check-code',[WarehouseController::class,'generateStockCheckCode']);
    Route::post('create-stock-check',[WarehouseController::class,'createStockCheck']);
    Route::post('update-stock-check',[WarehouseController::class,'updateStockCheck']);
    Route::post('send-stock-check',[WarehouseController::class,'sendStockCheck']);
    Route::post('delete-stock-check',[WarehouseController::class,'deleteStockCheck']);
    Route::post('add-stock-check-stocks',[WarehouseController::class,'addStockCheckStocks']);
    Route::post('update-stock-check-stock',[WarehouseController::class,'updateStockCheckStock']);
    Route::post('stock-check-details',[WarehouseController::class,'stockCheckDetails']);
    Route::post('stock-check-delete-list',[WarehouseController::class,'stockCheckDeleteList']);
    #################### vendor discounts ################
    Route::get('discounts',[AdminstarionController::class,'discounts']);
    Route::get('generate-dicounts-code',[AdminstarionController::class,'generateDicountsCode']);
    Route::post('create-discount',[AdminstarionController::class,'createDiscount']);
    Route::post('update-discount',[AdminstarionController::class,'updateDiscount']);
    Route::post('delete-discount',[AdminstarionController::class,'deleteDiscount']);
    Route::post('discount-details',[AdminstarionController::class,'discountDetails']);
    Route::post('add-discount-branche',[AdminstarionController::class,'addDiscountBranche']);
    Route::post('delete-discount-branche',[AdminstarionController::class,'deleteDiscountBranche']);
    Route::post('add-discount-product-categories',[AdminstarionController::class,'addDiscountProductCategories']);
    Route::post('delete-discount-product-category',[AdminstarionController::class,'deleteDiscountProductCategory']);
    Route::post('add-discount-products',[AdminstarionController::class,'addDiscountProducts']);
    Route::post('delete-discount-product',[AdminstarionController::class,'deleteDiscountProduct']);
    Route::post('add-discount-product-collection',[AdminstarionController::class,'addDiscountProductCollection']);
    Route::post('delete-discount-product-collection',[AdminstarionController::class,'deleteDiscountProductCollection']);
    Route::post('add-discount-tags',[AdminstarionController::class,'addDiscountTags']);
    Route::post('delete-discount-tags',[AdminstarionController::class,'deleteDiscountTags']);
    Route::post('discounts-add-tags',[AdminstarionController::class,'discountsAddTags']);
    Route::post('discounts-delete-tags',[AdminstarionController::class,'discountsDeleteTags']);
    Route::post('discounts-delete-list',[AdminstarionController::class,'discountsDeleteList']);
    ######################## coupons ##########################
    Route::post('coupons',[AdminstarionController::class,'coupons']);
    Route::post('create-coupon',[AdminstarionController::class,'createCoupon']);
    Route::post('update-coupon',[AdminstarionController::class,'updateCoupon']);
    Route::post('delete-coupon',[AdminstarionController::class,'deleteCoupon']);
    Route::post('coupons-delete-list',[AdminstarionController::class,'couponsDeleteList']);
    Route::post('coupons-restore-list',[AdminstarionController::class,'couponsRestoreList']);
    Route::post('coupons-restore-single-item',[AdminstarionController::class,'couponsRestoreSingleItem']);
    Route::post('coupons-active-list',[AdminstarionController::class,'couponsActiveList']);
    #################### prompotions ####################
    Route::post('promotion-offers',[AdminstarionController::class,'promotionOffers']);
    Route::post('create-promotion-offer',[AdminstarionController::class,'createPromotionOffer']);
    Route::post('update-promotion-offer',[AdminstarionController::class,'updatePromotionOffer']);
    Route::post('promotion-details',[AdminstarionController::class,'promotionOfferDetails']);
    Route::post('delete-promotion-offer',[AdminstarionController::class,'deletePromotionOffer']);
    Route::post('add-promotion-tags',[AdminstarionController::class,'addPromotionTags']);
    Route::post('delete-promotion-tags',[AdminstarionController::class,'deletePromotionTags']);
    Route::post('add-promotion-branches',[AdminstarionController::class,'addPromotionBranches']);
    Route::post('delete-promotion-branches',[AdminstarionController::class,'deletePromotionBranches']);
    Route::post('promotions-delete-list',[AdminstarionController::class,'promotionsDeleteList']);
    Route::post('promotions-restore-list',[AdminstarionController::class,'promotionsRestoreList']);
    Route::post('promotions-restore-single-item',[AdminstarionController::class,'promotionsRestoreSingleItem']);
    Route::post('promotions-active-list',[AdminstarionController::class,'promotionsActiveList']);
    ################# work shift #####################
    Route::get('work-shifts',[AdminstarionController::class,'workShifts']);
    Route::get('generate-shift-code',[AdminstarionController::class,'generateShiftCode']);
    Route::post('create-shift',[AdminstarionController::class,'createShift']);
    Route::post('update-shift',[AdminstarionController::class,'updateShift']);
    Route::post('delete-shift',[AdminstarionController::class,'deleteShift']);
    Route::post('shifts-delete-list',[AdminstarionController::class,'shiftsDeleteList']);
    ################## temporary events ####################
    Route::get('temporary-events',[AdminstarionController::class,'temporaryEvents']);
    Route::post('create-temporary-events',[AdminstarionController::class,'createTemporaryEvents']);
    Route::post('update-temporary-events',[AdminstarionController::class,'updateTemporaryEvents']);
    Route::post('delete-temporary-events',[AdminstarionController::class,'deleteTemporaryEvents']);
    Route::post('temporary-events-details',[AdminstarionController::class,'temporaryEventsDetails']);
    Route::post('add-temporary-event-branch',[AdminstarionController::class,'addTemporaryEventBranche']);
    Route::post('delete-temporary-event-branch',[AdminstarionController::class,'deleteTemporaryEventBranche']);
    Route::post('add-temporary-event-product-categories',[AdminstarionController::class,'addTemporaryEventProductCategories']);
    Route::post('delete-temporary-event-product-category',[AdminstarionController::class,'deleteTemporaryEventProductCategory']);
    Route::post('add-temporary-event-products',[AdminstarionController::class,'addTemporaryEventProducts']);
    Route::post('delete-temporary-event-product',[AdminstarionController::class,'deleteTemporaryEventProduct']);
    Route::post('add-temporary-event-product-collection',[AdminstarionController::class,'addTemporaryEventProductCollection']);
    Route::post('delete-temporary-event-product-collection',[AdminstarionController::class,'deleteTemporaryEventProductCollection']);
    Route::post('add-temporary-event-tags',[AdminstarionController::class,'addTemporaryEventTags']);
    Route::post('delete-temporary-event-tags',[AdminstarionController::class,'deleteTemporaryEventTags']);
    Route::post('temporary-event-add-tags',[AdminstarionController::class,'temporaryEventAddTags']);
    Route::post('temporary-event-delete-tags',[AdminstarionController::class,'temporaryEventDeleteTags']);
    Route::post('temporary-event-delete-list',[AdminstarionController::class,'temporaryEventDeleteList']);
    ####################### vendor custodians #####################
    Route::get('vendor-custodians',[AdminstarionController::class,'vendorCustodians']);
    #################### devices #########################
    Route::get('device-types',[AdminstarionController::class,'getDeviceTypes']);
    Route::post('devices',[AdminstarionController::class,'devices']);
    Route::get('generate-device-code',[AdminstarionController::class,'generateDeviceCode']);
    Route::post('create-device',[AdminstarionController::class,'createDevice']);
    Route::post('update-device',[AdminstarionController::class,'updateDevice']);
    Route::post('device-details',[AdminstarionController::class,'deviceDetails']);
    Route::post('delete-device',[AdminstarionController::class,'deleteDevice']);
    Route::post('add-device-tags',[AdminstarionController::class,'addDeviceTags']);
    Route::post('delete-device-tags',[AdminstarionController::class,'deleteDeviceTags']);
    Route::post('add-cashier-device-setting',[AdminstarionController::class,'addCashierDevice']);
    #################### payment methods ##################3
    Route::get('payment-method',[AdminstarionController::class,'getPaymentMethod']);
    Route::post('create-payment-method',[AdminstarionController::class,'createPaymentMethod']);
    Route::post('update-payment-method',[AdminstarionController::class,'updatePaymentMethod']);
    Route::post('delete-payment-method',[AdminstarionController::class,'deletePaymentMethod']);
    Route::post('sort-payment-method',[AdminstarionController::class,'sortPaymentMethod']);
    ################### fees ##################################
    Route::get('fees',[AdminstarionController::class,'getFees']);
    Route::post('create-fee',[AdminstarionController::class,'createFees']);
    Route::post('update-fee',[AdminstarionController::class,'updateFees']);
    Route::post('delete-fee',[AdminstarionController::class,'deleteFees']);
    ################# preparation track ##################
    Route::post('preparation-tracks',[AdminstarionController::class,'getPreparationTracks']);
    Route::post('create-preparation-track',[AdminstarionController::class,'createPreparationTracks']);
    Route::post('update-preparation-track',[AdminstarionController::class,'updatePreparationTracks']);
    Route::post('delete-preparation-track',[AdminstarionController::class,'deletePreparationTracks']);
    ##################### order types ##################
    Route::get('order-types',[AdminstarionController::class,'getOrderTypes']);
    Route::post('create-order-types',[AdminstarionController::class,'createOrderTypes']);
    Route::post('update-order-types',[AdminstarionController::class,'updateOrderTypes']);
    Route::post('delete-order-types',[AdminstarionController::class,'deleteOrderTypes']);
    ################ branch booking tables #####################
    Route::get('branch-booking-tables',[AdminstarionController::class,'getBranchBookingTables']);
    Route::post('create-branch-booking-tables',[AdminstarionController::class,'createBranchBookingTables']);
    Route::post('update-branch-booking-tables',[AdminstarionController::class,'updateBranchBookingTables']);
    Route::post('delete-branch-booking-tables',[AdminstarionController::class,'deleteBranchBookingTables']);
    Route::post('branch-booking-table-details',[AdminstarionController::class,'branchBookingTablesDetails']);
    Route::post('tables-add-category',[AdminstarionController::class,'tablesAddCategory']);
    Route::post('tables-delete-list',[AdminstarionController::class,'tablesDeleteList']);
    ################# vendor setting ####################
    Route::get('vendor-setting',[AdminstarionController::class,'vendorSetting']);
    Route::post('save-vendor-settings',[AdminstarionController::class,'saveVendorSettings']);
    ############ charities #################
    Route::get('charities',[AdminstarionController::class,'charities']);
    Route::post('add-charities',[AdminstarionController::class,'addCharities']);
    Route::get('protection-systems',[AdminstarionController::class,'protectionSystems']);
    Route::post('add-protection-systems',[AdminstarionController::class,'addProtectionSystems']);
    Route::get('notification-active',[AdminstarionController::class,'notifacationActive']);
    Route::post('add-notification-active',[AdminstarionController::class,'addNotifacationActive']);
    Route::post('add-notify-branch',[AdminstarionController::class,'addNotifyBranch']);
    Route::get('tables-categories',[AdminstarionController::class,'tablesCategories']);
    Route::post('add-tables-categories',[AdminstarionController::class,'addTablesCategories']);
    Route::post('tables-categories-delete-list',[AdminstarionController::class,'tablesCategoriesDeleteList']);
    Route::post('delete-tables-categories',[AdminstarionController::class,'deleteTablesCategories']);
    Route::get('tables-data',[AdminstarionController::class,'tablesData']);
    Route::post('add-tables',[AdminstarionController::class,'addTables']);
    Route::post('delete-table',[AdminstarionController::class,'deleteTable']);
    Route::post('tables-delete-list',[AdminstarionController::class,'tablesDeleteList']);
    Route::post('campaigns',[AdminstarionController::class,'campaigns']);
    Route::post('create-campaigns',[AdminstarionController::class,'createCampaigns']);
    Route::post('campaign-details',[AdminstarionController::class,'campaignDetails']);
    Route::post('delete-campaign',[AdminstarionController::class,'deleteCampaign']);
    Route::post('campaigns-delete-list',[AdminstarionController::class,'campaignsDeleteList']);
    Route::get('vendor-barcodes',[AdminstarionController::class,'vendorBarcodes']);
    Route::get('generate-barcode-product',[AdminstarionController::class,'generateBarcodeProduct']);
    Route::post('create-barcode',[AdminstarionController::class,'createBarcode']);
    ################### orders ###################
    Route::post('add-order-tags',[OrdersController::class,'addOrderTags']);
    Route::post('delete-order-tag',[OrdersController::class,'deleteOrderTag']);
    Route::post('orders-add-tags-list',[OrdersController::class,'orderAddTagsList']);
    Route::post('orders-delete-tags-list',[OrdersController::class,'orderDeleteTagsList']);
    Route::post('orders-list-delete',[OrdersController::class,'orderListDelete']);
});
