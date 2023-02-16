<?php

use Illuminate\Support\Facades\Route;
use Spatie\SimpleExcel\SimpleExcelWriter;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');

//    return \App\Models\VendorStatus::find(1);

});
Route::get('make-migrate',function (){
    \Illuminate\Support\Facades\Artisan::call('migrate');
    return 'migrated';
});
Route::get('roolback-migrate',function (){
    \Illuminate\Support\Facades\Artisan::call('migrate:rollback --step=1');
    return 'roolback';
});
Route::get('export-sup',function (){
    $categories = \App\Models\ProductCategory::get();
    $writer = SimpleExcelWriter::streamDownload('categories.xlsx');
        foreach ($categories as $cat) {
            $writer->addRow([
                'name_ar' => $cat->name_ar,
                'name_en' => $cat->name_en,
                'operation_number' => $cat->operation_number,
                'status' => $cat->status,
                'sort' => $cat->sort,
            ]);
                }
        $writer->toBrowser();
});
