<?php

use App\Http\Controllers\SubUserController;
use App\Http\Controllers\LineCardController;
use App\Http\Controllers\SuperAdmin\MainUserController as SuperAdminMainUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('home');
});

Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();


Route::get('generator_builder', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@builder')->name('io_generator_builder');

Route::get('field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@fieldTemplate')->name('io_field_template');

Route::get('relation_field_template', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@relationFieldTemplate')->name('io_relation_field_template');

Route::post('generator_builder/generate', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generate')->name('io_generator_builder_generate');

Route::post('generator_builder/rollback', '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@rollback')->name('io_generator_builder_rollback');

Route::post(
    'generator_builder/generate-from-file',
    '\InfyOm\GeneratorBuilder\Controllers\GeneratorBuilderController@generateFromFile'
)->name('io_generator_builder_generate_from_file');

Route::prefix('admin')->group(function () {

    // 超級管理員路由
    Route::middleware(['auth', 'check.super.admin', 'check.main'])->group(function () {
        Route::any('adminUsers', [App\Http\Controllers\Admin\AdminAccountController::class, 'index'])->name('admin.adminUsers.index');
        Route::any('adminUsers/create', [App\Http\Controllers\Admin\AdminAccountController::class, 'create'])->name('admin.adminUsers.create');
        Route::any('adminUsers/store', [App\Http\Controllers\Admin\AdminAccountController::class, 'store'])->name('admin.adminUsers.store');
        Route::any('adminUsers/show/{id}', [App\Http\Controllers\Admin\AdminAccountController::class, 'show'])->name('admin.adminUsers.show');
        Route::any('adminUsers/edit/{id}', [App\Http\Controllers\Admin\AdminAccountController::class, 'edit'])->name('admin.adminUsers.edit');
        Route::any('adminUsers/update/{id}', [App\Http\Controllers\Admin\AdminAccountController::class, 'update'])->name('admin.adminUsers.update');
        Route::any('adminUsers/destroy/{id}', [App\Http\Controllers\Admin\AdminAccountController::class, 'destroy'])->name('admin.adminUsers.destroy');
    });


    // 主帳號管理路由
    // 這些路由僅限主帳號和超級管理員訪問
    Route::middleware(['auth', 'check.super.admin'])->group(function () {
        // Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('main-users', SuperAdminMainUserController::class)
            ->names([
                'index' => 'super_admin.mainUsers.index',
                'store' => 'super_admin.mainUsers.store',
                'show' => 'super_admin.mainUsers.show',
                'update' => 'super_admin.mainUsers.update',
                'destroy' => 'super_admin.mainUsers.destroy',
                'create' => 'super_admin.mainUsers.create',
                'edit' => 'super_admin.mainUsers.edit'
            ]);
    });

    // 主帳號與子帳號的路由
    // 這些路由僅限主帳號和超級管理員訪問
    Route::middleware(['auth', 'check.super.admin', 'check.main'])->group(function () {
        Route::get('/sub-users', [SubUserController::class, 'index'])->name('sub-users.index');
        Route::get('/sub-users/create', [SubUserController::class, 'create'])->name('sub-users.create');
        Route::post('/sub-users', [SubUserController::class, 'store'])->name('sub-users.store');
        Route::get('/sub-users/{sub}/edit', [SubUserController::class, 'edit'])->name('sub-users.edit');
        Route::put('/sub-users/{sub}', [SubUserController::class, 'update'])->name('sub-users.update');
        Route::delete('/sub-users/{sub}', [SubUserController::class, 'destroy'])->name('sub-users.destroy');

        // 主帳號可查看所有子帳號的電子名片
        Route::get('/all-cards', [App\Http\Controllers\Admin\BusinessCardsController::class, 'allCards'])->name('admin.all-cards');
    });

    // 所有已登入用戶可訪問的路由
    Route::middleware(['auth', 'check.active'])->group(function () {
        Route::resource('card-templates', App\Http\Controllers\Admin\CardTemplatesController::class)
            ->names([
                'index' => 'admin.cardTemplates.index',
                'store' => 'admin.cardTemplates.store',
                'show' => 'admin.cardTemplates.show',
                'update' => 'admin.cardTemplates.update',
                'destroy' => 'admin.cardTemplates.destroy',
                'create' => 'admin.cardTemplates.create',
                'edit' => 'admin.cardTemplates.edit'
            ]);

        // 電子名片資源路由 (包含權限控制)
        Route::resource('business-cards', App\Http\Controllers\Admin\BusinessCardsController::class)
            ->names([
                'index' => 'admin.businessCards.index',
                'store' => 'admin.businessCards.store',
                'show' => 'admin.businessCards.show',
                'update' => 'admin.businessCards.update',
                'destroy' => 'admin.businessCards.destroy',
                'create' => 'admin.businessCards.create',
                'edit' => 'admin.businessCards.edit'
            ]);

        Route::get('business-cards/preview/{uuid}', [App\Http\Controllers\Admin\BusinessCardsController::class, 'preview'])->name('admin.businessCards.preview');

        // LINE電子名片預覽與分享
        // Route::get('/preview-card/{id}', [LineCardController::class, 'preview'])->name('admin.preview-card');
        // Route::get('/share-card/{id}', [LineCardController::class, 'share'])->name('admin.share-card');

    });
});

// 電子名片管理相關路由
Route::middleware(['auth', 'check.active'])->prefix('admin')->name('admin.')->group(function () {
    // 電子名片基本管理
    Route::resource('businessCards', App\Http\Controllers\Admin\BusinessCardsController::class);

    // 查看所有子帳號的電子名片
    Route::get('business-cards/all-cards', [App\Http\Controllers\Admin\BusinessCardsController::class, 'allCards'])
        ->name('businessCards.allCards');

    // 重新生成 Flex JSON
    Route::post('business-cards/{businessCard}/regenerate-flex', [App\Http\Controllers\Admin\BusinessCardsController::class, 'regenerateFlexJson'])
        ->name('businessCards.regenerateFlexJson');

    // 氣泡卡片管理
    Route::get('business-cards/{businessCard}/bubbles', [App\Http\Controllers\Admin\CardBubblesController::class, 'index'])
        ->name('businessCards.bubbles.index');

    Route::get('business-cards/{businessCard}/bubbles/create', [App\Http\Controllers\Admin\CardBubblesController::class, 'create'])
        ->name('businessCards.bubbles.create');

    Route::post('business-cards/{businessCard}/bubbles', [App\Http\Controllers\Admin\CardBubblesController::class, 'store'])
        ->name('businessCards.bubbles.store');

    Route::get('business-cards/{businessCard}/bubbles/{bubble}', [App\Http\Controllers\Admin\CardBubblesController::class, 'show'])
        ->name('businessCards.bubbles.show');

    Route::get('business-cards/{businessCard}/bubbles/{bubble}/edit', [App\Http\Controllers\Admin\CardBubblesController::class, 'edit'])
        ->name('businessCards.bubbles.edit');

    Route::patch('business-cards/{businessCard}/bubbles/{bubble}', [App\Http\Controllers\Admin\CardBubblesController::class, 'update'])
        ->name('businessCards.bubbles.update');

    Route::delete('business-cards/{businessCard}/bubbles/{bubble}', [App\Http\Controllers\Admin\CardBubblesController::class, 'destroy'])
        ->name('businessCards.bubbles.destroy');

    Route::post('business-cards/{businessCard}/bubbles/reorder', [App\Http\Controllers\Admin\CardBubblesController::class, 'reorder'])
        ->name('businessCards.bubbles.reorder');
});

// 前台分享路由
Route::get('/share/{uuid}', [App\Http\Controllers\Admin\BusinessCardsController::class, 'preview'])
    ->name('businessCards.preview');

// 公開的LINE卡片顯示頁面
// Route::get('/line-card/{uuid}', [LineCardController::class, 'show'])->name('line.card.show');
Route::get('/liff/{uuid?}', [LineCardController::class, 'liff'])->name('line.card.liff');
Route::get('/liff', [LineCardController::class, 'liff'])->name('line.card.liff.default'); // 新增無參數的路由
Route::get('/liff-api/send/{uuid}', [LineCardController::class, 'send'])->name('line.card.send');
Route::get('/share/{uuid}', [LineCardController::class, 'share'])->name('line.card.share');

