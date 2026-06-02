<?php

use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ImageCallbackController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;


// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);

// Image service callback (authenticated via API key)
Route::post('/image-callback', [ImageCallbackController::class, 'handle']);



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Listings
    Route::post('/listings', [ListingController::class, 'store']);
    Route::put('/listings/{listing}', [ListingController::class, 'update']);
    Route::delete('/listings/{listing}', [ListingController::class, 'destroy']);
    Route::get('/user/listings', [ListingController::class, 'userListings']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/listings/{listing}/favorite', [FavoriteController::class, 'toggle']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'sendMessage']);

    // Wallet
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::get('/wallet/providers', [WalletController::class, 'providers']);

    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);
    Route::post('/payment-methods/{paymentMethod}/default', [PaymentMethodController::class, 'setDefault']);

    // Purchases
    Route::post('/listings/{listing}/buy', [PurchaseController::class, 'store']);
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/sales', [PurchaseController::class, 'sales']);
    Route::post('/purchases/{purchase}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::get('/produits', [ProduitController::class, 'index']);
    Route::post('/produits', [ProduitController::class, 'store']);
    Route::get('/produits/{produit}', [ProduitController::class, 'show']);
    Route::put('/produits/{produit}', [ProduitController::class, 'update']);
    Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

    // Admin
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Users
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::put('/users/{user}/password', [AdminUserController::class, 'updatePassword']);
        Route::post('/users/{user}/ban', [AdminUserController::class, 'ban']);
        Route::post('/users/{user}/unban', [AdminUserController::class, 'unban']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        // Listings
        Route::get('/listings', [AdminListingController::class, 'index']);
        Route::get('/listings/{listing}', [AdminListingController::class, 'show']);
        Route::put('/listings/{listing}', [AdminListingController::class, 'update']);
        Route::delete('/listings/{listing}', [AdminListingController::class, 'destroy']);
    });
});

