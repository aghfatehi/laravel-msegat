<?php

/*
|--------------------------------------------------------------------------
| Msegat Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming callbacks from Msegat for delivery reports,
| message status updates, incoming replies, and failed message alerts.
| All routes are prefixed with 'webhook/msegat'.
|
*/

use Aghfatehi\Msegat\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhook/msegat')->name('msegat.webhook.')->group(function () {
    Route::post('delivery', [WebhookController::class, 'deliveryReport'])->name('delivery');
    Route::post('status', [WebhookController::class, 'status'])->name('status');
    Route::post('incoming', [WebhookController::class, 'incoming'])->name('incoming');
    Route::post('failed', [WebhookController::class, 'failed'])->name('failed');
});
