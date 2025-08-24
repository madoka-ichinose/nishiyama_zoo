<?php

use App\Http\Controllers\TopController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\Admin\ContestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ContestPhotoController;

// ==========================
// 公開（誰でも閲覧可）
// ==========================

// トップ
Route::get('/', [TopController::class, 'top'])->name('top');

// 動物図鑑
Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
Route::get('/animals/{animal}', [AnimalController::class, 'show'])->name('animals.show');

// 写真（公開一覧・詳細は承認済のみを表示）
Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');

Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
Route::get('/info', [InfoController::class, 'index'])->name('info.index');

// メール認証
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ==========================
// 会員（要ログイン）
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {
    // マイページ（自分の投稿一覧・管理）
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage.index');

    // 自分の投稿の削除（本人のみ、Policyで制御）
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    // いいね機能（任意）
    // Route::post('/photos/{photo}/likes', [LikeController::class, 'store'])->name('photos.likes.store');
    // Route::delete('/photos/{photo}/likes', [LikeController::class, 'destroy'])->name('photos.likes.destroy');
});

// ==========================
// 投稿（要ログイン + メール認証）
// Fortifyのメール認証を使う場合は 'verified' を付与
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/photos/create', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('/photos', [PhotoController::class, 'store'])->name('photos.store');
});

Route::get('/photos/{photo}', [PhotoController::class, 'show'])->name('photos.show');

// ==========================
// 管理（要ログイン + isAdmin）
// ==========================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
});

Route::prefix('admin')
    ->as('admin.')
    ->middleware(['auth', 'can:isAdmin'])
    ->group(function () {
        // ダッシュボード
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // 会員管理（閲覧のみ例）
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/promote', [UserController::class,'promote'])->name('users.promote');
        Route::put('/users/{user}/demote',  [UserController::class,'demote'])->name('users.demote');
        Route::delete('/users/{user}',      [UserController::class,'destroy'])->name('users.destroy');

        // 投稿管理：承認待ち/承認済み/非公開（soft delete）一覧
        Route::get('/photos', [AdminPhotoController::class, 'index'])->name('photos.index');
        Route::put('/photos/{photo}/approve', [AdminPhotoController::class, 'approve'])->name('photos.approve');
        Route::delete('/photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');
        Route::put('/photos/{photo}/hide',   [AdminPhotoController::class, 'hide'])->name('photos.hide');
        Route::put('/photos/{photo}/unhide', [AdminPhotoController::class, 'unhide'])->name('photos.unhide');

        // コンテスト管理
        Route::get('/contests', [ContestController::class, 'index'])->name('contests.index');
        Route::post('/contests', [ContestController::class, 'store'])->name('contests.store');
        Route::put('/contests/{contest}/end-now', [ContestController::class,'endNow'])->name('contests.endNow');
        Route::get('/contests/{contest}/edit', [\App\Http\Controllers\Admin\ContestController::class, 'edit'])
    ->name('contests.edit');
        Route::put('/contests/{contest}',        [ContestController::class,'update'])->name('contests.update');
        Route::delete('/contests/{contest}',     [ContestController::class,'destroy'])->name('contests.destroy');
        // 応募写真 一覧/集計/CSV/一括操作（コンテスト単位）
        Route::get('/contests/{contest}/photos', [ContestPhotoController::class, 'index'])->name('contests.photos.index');
        Route::post('/contests/{contest}/photos/bulk', [ContestPhotoController::class, 'bulkUpdate'])->name('contests.photos.bulk');
        Route::get('/contests/{contest}/photos/export', [ContestPhotoController::class, 'exportCsv'])->name('contests.photos.export');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });

// ==========================
// 404 フォールバック（任意）
// ==========================
// Route::fallback(fn() => abort(404));
