<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuController::class, 'home'])->name('home');
Route::get('/thuc-don', [MenuController::class, 'index'])->name('menu');
Route::get('/mon-an/{dish}', [MenuController::class, 'show'])->name('dishes.show');
Route::get('/dat-ban', [ReservationController::class, 'create'])->name('reservations.create');
Route::post('/dat-ban', [ReservationController::class, 'store'])->middleware('throttle:public-forms')->name('reservations.store');
Route::get('/lien-he', [ContactController::class, 'create'])->name('contacts.create');
Route::post('/lien-he', [ContactController::class, 'store'])->middleware('throttle:public-forms')->name('contacts.store');
Route::middleware('guest')->group(function (): void {
    Route::get('/dang-nhap', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/dang-ky', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:public-forms')->name('register.store');
    Route::get('/quen-mat-khau', [AuthController::class, 'forgotForm'])->name('password.request');
    Route::post('/quen-mat-khau', [AuthController::class, 'forgot'])->middleware('throttle:public-forms')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [AuthController::class, 'reset'])->middleware('throttle:public-forms')->name('password.update');
});
Route::middleware('auth')->group(function (): void {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::get('/ho-so', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/ho-so', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/lich-dat-ban', [ReservationController::class, 'index'])->name('reservations.index');
    Route::patch('/lich-dat-ban/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/danh-gia/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});
Route::middleware(['auth', 'role:user'])->group(function (): void {
    Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/gio-hang/{dish}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/gio-hang/{dish}', [CartController::class, 'update'])->whereNumber('dish')->name('cart.update');
    Route::delete('/gio-hang/{dish}', [CartController::class, 'destroy'])->whereNumber('dish')->name('cart.destroy');
    Route::post('/dat-mon', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/don-hang/{order}/bao-chuyen-khoan', [OrderController::class, 'reportPayment'])->name('orders.payment.report');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('/dia-chi', AddressController::class)->parameters(['dia-chi' => 'address'])->names('addresses');
    Route::get('/doi-voucher', [ProfileController::class, 'vouchers'])->name('vouchers.index');
    Route::post('/doi-voucher/{voucher}', [ProfileController::class, 'redeem'])->name('vouchers.redeem');
    Route::post('/mon-an/{dish}/danh-gia', [ReviewController::class, 'store'])->middleware('throttle:public-forms')->name('reviews.store');
    Route::put('/danh-gia/{review}', [ReviewController::class, 'update'])->name('reviews.update');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,owner', 'can:admin'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->middleware('can:view-reports')->name('dashboard');
    Route::get('/orders', [OrderController::class, 'management'])->name('orders.index');
    Route::post('/orders/{order}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.payment.verify');
    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::post('/dishes/bulk-delete', [DishController::class, 'bulkDestroy'])->name('dishes.bulk-destroy');
    Route::delete('/dishes/{dish}/images/{image}', [DishController::class, 'deleteImage'])->name('dishes.images.destroy');
    Route::resource('dishes', DishController::class);
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::patch('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::middleware(['role:owner', 'can:owner'])->group(function (): void {
        Route::resource('vouchers', VoucherController::class);
    });
    Route::prefix('accounts/{group}')->whereIn('group', ['staff', 'customers'])->middleware('can:manage-accounts,group')->name('accounts.')->group(function (): void {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/create', [AccountController::class, 'create'])->name('create');
        Route::post('/', [AccountController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [AccountController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::get('/{account}', [AccountController::class, 'show'])->name('show');
        Route::get('/{account}/edit', [AccountController::class, 'edit'])->name('edit');
        Route::put('/{account}', [AccountController::class, 'update'])->name('update');
        Route::delete('/{account}', [AccountController::class, 'destroy'])->name('destroy');
    });

});
