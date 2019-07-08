<?php

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
    return view('pages.inputs');
});
Route::get('/index', function () {
    return view('pages.product.index');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

//Product Routes
// Route::group(['prefix' => 'products', 'middleware' => ['auth']], function () {
// 	Route::get('/create','ProductController@createproduct')->name('createproduct');
// 	Route::get('/view','ProductController@index')->name('viewproduct');
// 	Route::post('/store','ProductController@storeproduct')->name('storeproduct');
// 	Route::get('/edit/{productid}','ProductController@editproduct')->name('editproduct');
// });
Route::get('product/index','ProductController@index')->name('viewproduct');
Route::get('product/create','ProductController@createproduct')->name('createproduct');
Route::post('product/store','ProductController@storeproduct')->name('storeproduct');
Route::get('product/edit/{productid}','ProductController@editproduct')->name('editproduct');
Route::post('product/update', 'ProductController@updateproduct')->name('updateproduct');
Route::get('product/delete/{productid}', 'ProductController@deleteproduct')->name('deleteproduct');

//Category Routes
Route::post('category/store','CategoryController@storecategory')->name('storecategory');

//Brand Routes
Route::post('brand/store','BrandController@storebrand')->name('storebrand');

//Tax Routes
Route::get('tax/index', 'TaxController@index')->name('viewtax');
Route::get('tax/getdata', 'TaxController@getdata')->name('gettax');
Route::post('tax/store', 'TaxController@storetax')->name('storetax');
Route::get('tax/edit', 'TaxController@edittax')->name('edittax');
Route::post('tax/udpate', 'TaxController@updatetax')->name('updatetax');
Route::get('tax/delete', 'TaxController@deletetax')->name('deletetax');

//Supplier Routes
Route::get('supplier/index', 'SupplierController@index')->name('viewsupplier');
Route::get('supplier/create', 'SupplierController@createsupplier')->name('createsupplier');
Route::post('supplier/store', 'SupplierController@storesupplier')->name('storesupplier');
Route::get('supplier/edit/{supplierid}', 'SupplierController@editsupplier')->name('editsupplier');
Route::post('supplier/update', 'SupplierController@updatesupplier')->name('updatesupplier');
Route::get('supplier/delete/{supplierid}', 'SupplierController@deletesupplier')->name('deletesupplier');

//Customer Routes
Route::get('customer/index', 'CustomerController@index')->name('viewcustomer');
Route::get('customer/create', 'CustomerController@createcustomer')->name('createcustomer');
Route::post('customer/store', 'CustomerController@storecustomer')->name('storecustomer');
Route::get('customer/edit/{customerid}', 'CustomerController@editcustomer')->name('editcustomer');
Route::post('customer/update', 'CustomerController@updatecustomer')->name('updatecustomer');
Route::get('customer/delete/{customerid}', 'CustomerController@deletecustomer')->name('deletecustomer');


