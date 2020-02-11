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
    return view('auth.login');
});
// Route::get('/index', function () {
//     return view('pages.product.index');
// });

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

//Product Routes
Route::group(['prefix' => 'product', 'middleware' => 'auth'], function(){
	Route::get('index','ProductController@index')->name('viewproduct');
	Route::get('create','ProductController@createproduct')->name('createproduct');
	Route::post('store','ProductController@storeproduct')->name('storeproduct');
	Route::get('edit/{productid}','ProductController@editproduct')->name('editproduct');
	Route::post('update', 'ProductController@updateproduct')->name('updateproduct');
	Route::get('delete/{productid}', 'ProductController@deleteproduct')->name('deleteproduct');
});

//Category Routes
Route::post('category/store','CategoryController@storecategory')->name('storecategory');

//Brand Routes
Route::post('brand/store','BrandController@storebrand')->name('storebrand');

//Tax Routes
Route::group(['prefix' => 'tax', 'middleware' => 'auth'], function(){
	Route::get('index', 'TaxController@index')->name('viewtax');
	Route::get('getdata', 'TaxController@getdata')->name('gettax');
	Route::post('store', 'TaxController@storetax')->name('storetax');
	Route::get('edit', 'TaxController@edittax')->name('edittax');
	Route::post('udpate', 'TaxController@updatetax')->name('updatetax');
	Route::get('delete', 'TaxController@deletetax')->name('deletetax');
});

//Supplier Routes
Route::group(['prefix' => 'supplier', 'middleware' => 'auth'], function(){
	Route::get('index', 'SupplierController@index')->name('viewsupplier');
	Route::get('create', 'SupplierController@createsupplier')->name('createsupplier');
	Route::post('store', 'SupplierController@storesupplier')->name('storesupplier');
	Route::get('edit/{supplierid}', 'SupplierController@editsupplier')->name('editsupplier');
	Route::post('update', 'SupplierController@updatesupplier')->name('updatesupplier');
	Route::get('delete/{supplierid}', 'SupplierController@deletesupplier')->name('deletesupplier');
});

//Customer Routes
Route::group(['prefix' => 'customer', 'middleware' => 'auth'], function(){
	Route::get('index', 'CustomerController@index')->name('viewcustomer');
	Route::get('create', 'CustomerController@createcustomer')->name('createcustomer');
	Route::post('store', 'CustomerController@storecustomer')->name('storecustomer');
	Route::get('edit/{customerid}', 'CustomerController@editcustomer')->name('editcustomer');
	Route::post('update', 'CustomerController@updatecustomer')->name('updatecustomer');
	Route::get('delete/{customerid}', 'CustomerController@deletecustomer')->name('deletecustomer');
});

//Purchase Order Routes
Route::group(['prefix' => 'PurchaseOrder', 'middleware' => ['auth']], function(){
	Route::get('create', 'PurchaseOrderController@createpo')->name('createpo');
});

//Xavier Routes
Route::group(['prefix' => 'xavier', 'middleware' => ['auth']], function(){
	Route::get('create', 'XavierController@createxavier')->name('createxavier');
});


