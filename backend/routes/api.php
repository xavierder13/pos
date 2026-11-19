<?php

use App\Http\Controllers\API\Administration\AuditTrailController;
use App\Http\Controllers\API\Administration\PermissionController;
use App\Http\Controllers\API\Administration\RoleController;
use App\Http\Controllers\API\Administration\SessionSettingController;
use App\Http\Controllers\API\Administration\UserController;
use App\Http\Controllers\API\Approvals\ApprovalController;
use App\Http\Controllers\API\Approvals\ApprovalFlowController;
use App\Http\Controllers\API\AttachmentController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Categories\CategoryController;
use App\Http\Controllers\API\Inventory\InventoryController;
use App\Http\Controllers\API\Locations\LocationController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\Products\ProductController;
use App\Http\Controllers\API\Register\RegisterCatalogController;
use Illuminate\Support\Facades\Route;

/*
| Every route but login: auth:sanctum + session.activity (Session Settings:
| idle time / session duration) + password.change (outside /auth) + the spatie `permission:` middleware
| of its <module>-<action> (`a|b` = either). The Administrator passes every
| check (Gate::before, AppServiceProvider). REST verbs; route names
| <module>.<action>. A new module: its routes here, its permissions in a
| seeder listed in PermissionSeeder::MODULES.
*/

Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->middleware('throttle:login')->name('login');
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:password-reset')->name('forgot-password');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:password-reset')->name('reset-password');

    Route::middleware(['auth:sanctum', 'session.activity'])->group(function () {
        Route::get('/init', 'init')->name('init');
        // the SPA's activity heartbeat / idle confirmation (passive)
        Route::get('/ping', 'ping')->name('ping');
        Route::post('/logout', 'logout')->name('logout');
        Route::put('/profile', 'updateProfile')->name('profile');
    });
});

Route::middleware(['auth:sanctum', 'session.activity', 'password.change'])->group(function () {

    // ── Notifications (the signed-in user's own) ─────────────────────
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/unread-count', 'unreadCount')->name('unread-count');
        Route::post('/read-all', 'markAllRead')->name('read-all');
        Route::post('/{id}/read', 'markRead')->name('read');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // ── Attachments (any App\Support\RecordTypes record: <type>-list to see, <type>-edit to change) ──
    Route::prefix('attachments')->name('attachments.')->controller(AttachmentController::class)->group(function () {
        Route::get('/{attachment}/download', 'download')->whereNumber('attachment')->name('download');
        Route::delete('/{attachment}', 'destroy')->whereNumber('attachment')->name('destroy');
        Route::get('/{type}/{id}', 'index')->whereNumber('id')->name('index');
        Route::post('/{type}/{id}', 'store')->whereNumber('id')->name('store');
    });

    // ── Approvals (everyone: what waits for them / what they requested; approval-list: all) ──
    Route::prefix('approvals')->name('approvals.')->controller(ApprovalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/submit', 'submit')->name('submit');
        Route::get('/record/{type}/{id}', 'forRecord')->whereNumber('id')->name('record');
        Route::get('/{approvalRequest}', 'show')->name('show');
        Route::post('/{approvalRequest}/approve', 'approve')->name('approve');
        Route::post('/{approvalRequest}/reject', 'reject')->name('reject');
        Route::post('/{approvalRequest}/cancel', 'cancel')->name('cancel');
    });

    Route::prefix('approval-flows')->name('approval-flows.')->controller(ApprovalFlowController::class)->middleware('permission:approval-flow-edit')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/options', 'options')->name('options');
        Route::post('/', 'store')->name('store');
        Route::put('/{approvalFlow}', 'update')->name('update');
        Route::delete('/{approvalFlow}', 'destroy')->name('destroy');
    });

    // ── Administration ───────────────────────────────────────────────
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:user-list')->name('index');
        Route::get('/form-options', 'formOptions')->middleware('permission:user-create|user-edit')->name('form-options');
        Route::get('/export', 'export')->middleware('permission:user-list')->name('export');
        Route::post('/', 'store')->middleware('permission:user-create')->name('store');
        Route::get('/{user}', 'show')->middleware('permission:user-list|user-edit')->name('show');
        Route::put('/{user}', 'update')->middleware('permission:user-edit')->name('update');
        Route::delete('/{user}', 'destroy')->middleware('permission:user-delete')->name('destroy');
    });

    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:role-list')->name('index');
        Route::get('/form-options', 'formOptions')->middleware('permission:role-create|role-edit')->name('form-options');
        Route::post('/', 'store')->middleware('permission:role-create')->name('store');
        Route::get('/{role}', 'show')->middleware('permission:role-list|role-edit')->name('show');
        Route::put('/{role}', 'update')->middleware('permission:role-edit')->name('update');
        // assign the role to many users at once
        Route::get('/{role}/users', 'users')->middleware('permission:role-edit')->name('users');
        Route::put('/{role}/users', 'assignUsers')->middleware('permission:role-edit')->name('users.assign');
        Route::delete('/{role}', 'destroy')->middleware('permission:role-delete')->name('destroy');
    });

    Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:permission-list')->name('index');
        // give one permission to many roles at once (it edits those roles: role-edit)
        Route::get('/role-options', 'roleOptions')->middleware('permission:role-edit')->name('role-options');
        Route::put('/{permission}/roles', 'assignRoles')->middleware('permission:role-edit')->name('roles.assign');
    });

    Route::get('/session-settings', [SessionSettingController::class, 'show'])->middleware('permission:session-setting-edit')->name('session-settings.show');
    Route::put('/session-settings', [SessionSettingController::class, 'update'])->middleware('permission:session-setting-edit')->name('session-settings.update');

    Route::prefix('audit-trail')->name('audit-trail.')->controller(AuditTrailController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:audit-trail-list')->name('index');
        Route::get('/options', 'options')->middleware('permission:audit-trail-list')->name('options');
    });

    // ── Locations (make:module) ─────────────────────────────────────
    Route::prefix('locations')->name('locations.')->controller(LocationController::class)->group(function () {
        // stock pages and the register pick locations too
        Route::get('/', 'index')->middleware('permission:location-list|inventory-list|register-use')->name('index');
        Route::post('/', 'store')->middleware('permission:location-create')->name('store');
        Route::get('/{location}', 'show')->middleware('permission:location-list|location-edit')->name('show');
        Route::put('/{location}', 'update')->middleware('permission:location-edit')->name('update');
        Route::delete('/{location}', 'destroy')->middleware('permission:location-delete')->name('destroy');
    });

    // ── Categories (make:module) ─────────────────────────────────────
    Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
        // the product form and filters pick categories too
        Route::get('/', 'index')->middleware('permission:category-list|product-list')->name('index');
        Route::post('/', 'store')->middleware('permission:category-create')->name('store');
        Route::get('/{category}', 'show')->middleware('permission:category-list|category-edit')->name('show');
        Route::put('/{category}', 'update')->middleware('permission:category-edit')->name('update');
        Route::delete('/{category}', 'destroy')->middleware('permission:category-delete')->name('destroy');
    });

    // ── Products (make:module) ─────────────────────────────────────
    Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
        Route::get('/', 'index')->middleware('permission:product-list')->name('index');
        Route::post('/', 'store')->middleware('permission:product-create')->name('store');
        Route::get('/{product}', 'show')->middleware('permission:product-list|product-edit')->name('show');
        Route::put('/{product}', 'update')->middleware('permission:product-edit')->name('update');
        Route::delete('/{product}', 'destroy')->middleware('permission:product-delete')->name('destroy');
    });

    // ── Inventory ───────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->controller(InventoryController::class)->group(function () {
        Route::get('/stock', 'stock')->middleware('permission:inventory-list')->name('stock');
        Route::get('/low-stock', 'lowStock')->middleware('permission:inventory-list')->name('low-stock');
        Route::get('/products/{product}/levels', 'levels')->middleware('permission:inventory-list|product-list')->name('levels');
        Route::get('/movements', 'movements')->middleware('permission:inventory-list')->name('movements');
        Route::get('/documents', 'documents')->middleware('permission:inventory-list')->name('documents');
        Route::get('/documents/{document}', 'document')->middleware('permission:inventory-list')->name('document');
        Route::post('/receipts', 'receive')->middleware('permission:inventory-receive')->name('receive');
        Route::post('/adjustments', 'adjust')->middleware('permission:inventory-adjust')->name('adjust');
        Route::post('/transfers', 'transfer')->middleware('permission:inventory-transfer')->name('transfer');
    });

    // ── Register (the sell screen's data) ───────────────────────
    Route::get('/register/catalog', RegisterCatalogController::class)->middleware('permission:register-use')->name('register.catalog');

    // [make:module routes] — `php artisan make:module` adds new modules' routes above this line
});
