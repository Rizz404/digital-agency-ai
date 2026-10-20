<?php

use App\Http\Controllers\Admin\ConsultantController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KnowledgeItemController;
use App\Http\Controllers\Admin\ServicePackageController;
use App\Http\Controllers\AgencyPageController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\StrategistChatController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AgencyPageController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('service-packages', ServicePackageController::class)->except('show');
        Route::resource('consultants', ConsultantController::class)->except('show');
        Route::resource('knowledge-items', KnowledgeItemController::class)->except('show');

        Route::get('consultations', [AdminConsultationController::class, 'index'])->name('consultations.index');
        Route::patch('consultations/{consultation}/status', [AdminConsultationController::class, 'updateStatus'])->name('consultations.status');

        Route::get('handovers', [HandoverController::class, 'index'])->name('handovers.index');
        Route::get('handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
        Route::post('handovers/{handover}/reply', [HandoverController::class, 'reply'])->name('handovers.reply');
        Route::post('handovers/{handover}/resolve', [HandoverController::class, 'resolve'])->name('handovers.resolve');
    });
});

Route::prefix('{agencySlug}')->middleware('agency')->group(function () {
    Route::get('/', [AgencyPageController::class, 'show'])->name('agency.show');
    Route::get('catalog', [AgencyPageController::class, 'catalog'])->name('agency.catalog');
    Route::get('catalog/{itemSlug}', [AgencyPageController::class, 'servicePackage'])->name('agency.service-package');
    Route::get('team', [AgencyPageController::class, 'team'])->name('agency.team');
    Route::get('team/{areaSlug}', [AgencyPageController::class, 'consultant'])->name('agency.consultant');
    Route::get('facilities', [AgencyPageController::class, 'facilities'])->name('agency.facilities');
    Route::get('facilities/{facilityId}', [AgencyPageController::class, 'facility'])->whereNumber('facilityId')->name('agency.facility');
    Route::get('info', [AgencyPageController::class, 'info'])->name('agency.info');
    Route::get('staff', [AgencyPageController::class, 'staff'])->name('agency.staff');
    Route::get('consultation', [AgencyPageController::class, 'consultationScene'])->name('agency.consultation');

    Route::prefix('consultation')->name('consultation.')->middleware('throttle:20,1')->group(function () {
        Route::post('quote', [ConsultationController::class, 'quote'])->name('quote');
        Route::post('/', [ConsultationController::class, 'store'])->name('store');
    });

    Route::prefix('strategist')->name('strategist.')->group(function () {
        Route::post('start', [StrategistChatController::class, 'start'])->middleware('throttle:strategist-start')->name('start');
        Route::post('message', [StrategistChatController::class, 'message'])->middleware('throttle:strategist-message')->name('message');
        Route::get('history', [StrategistChatController::class, 'history'])->middleware('throttle:strategist-history')->name('history');
        Route::post('speech', [StrategistChatController::class, 'speech'])->middleware('throttle:strategist-speech')->name('speech');
    });
});
