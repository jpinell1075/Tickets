<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Exports\ExcelExportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('exports')->group(function () {
    Route::post('excel', [ExcelExportController::class, 'excel']);
});