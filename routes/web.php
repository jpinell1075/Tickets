<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PanelController;

use App\Http\Controllers\AdministrationApp\PanelController as AdministrationAppPanelController;

use App\Http\Controllers\AdministrationApp\Users\UsersController;
use App\Http\Controllers\AdministrationApp\Users\UserTemplateController;
use App\Http\Controllers\Profile\ProfilesController;
use App\Http\Controllers\Profile\SocialAuthController;

use App\Http\Controllers\AdministrationApp\Backups\BackupsController;
use App\Http\Controllers\AdministrationApp\Backups\SchedulesController;

use App\Http\Controllers\AdministrationApp\Config\ConfigController;
use App\Http\Controllers\AdministrationApp\Config\LogsController;

use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\ModelFilters\ModelFiltersController;

use App\Http\Controllers\Exports\PrintController;

use App\Http\Controllers\AiApp\PanelController as AiAppPanelController;
use App\Http\Controllers\AiApp\Reports\AiReportsController;

use App\Http\Controllers\InventoryApp\PanelController as InventoryAppPanelController;
use App\Http\Controllers\TicketsApp\PanelController as TicketsAppPanelController;

use App\Http\Controllers\InventoryApp\Areas\AreasController;
use App\Http\Controllers\InventoryApp\Equipos\EquiposController;
use App\Http\Controllers\InventoryApp\Reportes\ReportesInventarioController;

use App\Http\Controllers\TicketsApp\Request\RequestController;
use App\Http\Controllers\TicketsApp\Maintenances\MaintenancesController;
use App\Http\Controllers\TicketsApp\TicketAdmin\TicketAdminController;
use App\Http\Controllers\TicketsApp\Reportes\ReportesTicketsController;

Route::middleware('HandleInertiaRequests')->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('panel');
        }
        return Inertia::render('login', [
            'global_use_social_login' => App\Models\Configurations\Configuration::where('key', 'global_use_social_login')->first()?->typed_value ?? false,
        ]);
    })->name('/');
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::get('login', function () {
        return redirect()->route('/');
    });
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('socialAuth')->group(function () {
        Route::get('redirect/{provider}/{state?}', [SocialAuthController::class, 'redirect'])->name('socialAuth.redirect');
        Route::get('callback/{provider}', [SocialAuthController::class, 'callback'])->name('socialAuth.callback');
        Route::delete('removeProvider/{userProvider}', [SocialAuthController::class, 'removeProvider'])->name('profile.removeProvider');
    });
});

Route::middleware(['auth', 'CheckCanLogin'])->group(function () {

    Route::get('/ping', function () { return response()->noContent(); })->name('ping');

    Route::middleware('HandleInertiaRequests')->group(function () {

        Route::get('panel', [PanelController::class, 'index'])->name('panel');

        Route::get('administration/panel', [AdministrationAppPanelController::class, 'index'])->name('administration_app.index');

        Route::prefix('profile')->group(function () {
            Route::get('index', [ProfilesController::class, 'index'])->name('profile.index');
            Route::put('update', [ProfilesController::class, 'update'])->name('profile.update');
            Route::put('changePassword', [ProfilesController::class, 'changePassword'])->name('profile.changePassword');
        });

        Route::prefix('users')->middleware('CheckRoles:users')->group(function () {
            Route::get('index', [UsersController::class, 'index'])->name('users.index');
            Route::get('show/{user}', [UsersController::class, 'show'])->name('users.show');
            Route::post('store', [UsersController::class, 'store'])->name('users.store');
            Route::put('update/{user}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('delete/{user}', [UsersController::class, 'delete'])->name('users.delete');

            Route::post('addModule', [UsersController::class, 'addModule'])->name('users.addModule');
            Route::delete('deleteModule/{userModule}/{user}', [UsersController::class, 'deleteModule'])->name('users.deleteModule');

            Route::post('addUserModelFilter/{user}', [UsersController::class, 'addUserModelFilter'])->name('users.addUserModelFilter');
            Route::delete('removeUserModelFilter/{userModelFilter}/{user}', [UsersController::class, 'removeUserModelFilter'])->name('users.removeUserModelFilter');

            Route::prefix('templates')->group(function () {
                Route::get('index', [UserTemplateController::class, 'index'])->name('users.templates.index');
                Route::get('show/{userTemplate}', [UserTemplateController::class, 'show'])->name('users.templates.show');
                Route::post('store', [UserTemplateController::class, 'store'])->name('users.templates.store');
                Route::put('update/{userTemplate}', [UserTemplateController::class, 'update'])->name('users.templates.update');
                Route::delete('delete/{userTemplate}', [UserTemplateController::class, 'delete'])->name('users.templates.delete');

                Route::post('addModule', [UserTemplateController::class, 'addModule'])->name('users.templates.addModule');
                Route::delete('deleteModule/{userTemplateModule}/{userTemplate}', [UserTemplateController::class, 'deleteModule'])->name('users.templates.deleteModule');

                Route::post('addUserTemplateModelFilter/{userTemplate}', [UserTemplateController::class, 'addUserTemplateModelFilter'])->name('users.templates.addUserTemplateModelFilter');
                Route::delete('removeUserTemplateModelFilter/{userTemplateModelFilter}/{userTemplate}', [UserTemplateController::class, 'removeUserTemplateModelFilter'])->name('users.templates.removeUserTemplateModelFilter');
            });
        });

        Route::prefix('backups')->middleware('CheckRoles:backups')->group(function () {
            Route::get('index', [BackupsController::class, 'index'])->name('backups.index');
            Route::post('store', [BackupsController::class, 'store'])->name('backups.store');
            Route::delete('delete/{name}', [BackupsController::class, 'delete'])->name('backups.delete');
            Route::get('download/{name}', [BackupsController::class, 'download'])->name('backups.download');

            Route::prefix('schedules')->group(function () {
                Route::put('update', [SchedulesController::class, 'update'])->name('backups.schedules.update');
            });
        });

        Route::prefix('configurations')->middleware('CheckRoles:config')->group(function () {
            Route::get('index', [ConfigController::class, 'index'])->name('config.index');
            Route::put('update/{configuration}', [ConfigController::class, 'update'])->name('config.update');
        });

        Route::prefix('logs')->middleware('CheckRoles:config')->group(function () {
            Route::get('index', [LogsController::class, 'index'])->name('logs.index');
            Route::delete('cleanUserLogs', [LogsController::class, 'cleanUserLogs'])->name('logs.cleanUserLogs');
        });

        Route::get('ai/panel', [AiAppPanelController::class, 'index'])->name('ai_assistant_app.index');
        Route::prefix('ai')->group(function () {
            Route::get('reports/querys', [AiReportsController::class, 'querys'])->name('ai_reports.index');
        });

        Route::prefix('inventory')->group(function () {
            Route::get('panel', [InventoryAppPanelController::class, 'index'])->name('inventory_app.index');

            Route::prefix('areas')->middleware('CheckRoles:areas')->group(function () {
                Route::get('index', [AreasController::class, 'index'])->name('areas.index');
                Route::get('show/{area}', [AreasController::class, 'show'])->name('areas.show');
                Route::post('store', [AreasController::class, 'store'])->name('areas.store');
                Route::put('update/{area}', [AreasController::class, 'update'])->name('areas.update');
                Route::delete('delete/{area}', [AreasController::class, 'delete'])->name('areas.delete');
            });

            Route::prefix('equipos')->middleware('CheckRoles:equipos')->group(function () {
                Route::get('index', [EquiposController::class, 'index'])->name('equipos.index');
                Route::get('show/{equipo}', [EquiposController::class, 'show'])->name('equipos.show');
                Route::post('store', [EquiposController::class, 'store'])->name('equipos.store');
                Route::put('update/{equipo}', [EquiposController::class, 'update'])->name('equipos.update');
                Route::delete('delete/{equipo}', [EquiposController::class, 'delete'])->name('equipos.delete');
                Route::post('addComment', [EquiposController::class, 'addComment'])->name('equipos.addComment');
                Route::delete('deleteComment/{comentario}', [EquiposController::class, 'deleteComment'])->name('equipos.deleteComment');
                Route::post('addFile', [EquiposController::class, 'addFile'])->name('equipos.addFile');
                Route::delete('deleteFile/{archivo}', [EquiposController::class, 'deleteFile'])->name('equipos.deleteFile');
                Route::get('downloadFile/{archivo}', [EquiposController::class, 'downloadFile'])->name('equipos.downloadFile');
                Route::post('addMaintenance', [EquiposController::class, 'addMaintenance'])->name('equipos.addMaintenance');
                Route::delete('deleteMaintenance/{mantenimiento}', [EquiposController::class, 'deleteMaintenance'])->name('equipos.deleteMaintenance');
                Route::post('addPeripheral', [EquiposController::class, 'addPeripheral'])->name('equipos.addPeripheral');
                Route::post('updatePeripheral/{periferico}', [EquiposController::class, 'updatePeripheral'])->name('equipos.updatePeripheral');
                Route::delete('deletePeripheral/{periferico}', [EquiposController::class, 'deletePeripheral'])->name('equipos.deletePeripheral');
                Route::post('addLoan', [EquiposController::class, 'addLoan'])->name('equipos.addLoan');
                Route::post('returnLoan/{prestamo}', [EquiposController::class, 'returnLoan'])->name('equipos.returnLoan');
            });

            Route::prefix('reportes_inventario')->middleware('CheckRoles:inventory_reports')->group(function () {
                Route::get('index', [ReportesInventarioController::class, 'index'])->name('inventory_reports.index');
                Route::get('generateGeneralReport', [ReportesInventarioController::class, 'generateGeneralReport'])->name('inventory_reports.generateGeneralReport');
            });
        });

        Route::prefix('tickets')->group(function () {
            Route::get('panel', [TicketsAppPanelController::class, 'index'])->name('tickets_app.index');

            Route::prefix('requests')->middleware('CheckRoles:requests')->group(function () {
                Route::get('index', [RequestController::class, 'index'])->name('requests.index');
                Route::post('store', [RequestController::class, 'store'])->name('requests.store');
                Route::get('history', [RequestController::class, 'history'])->name('requests.history');
            });

            Route::prefix('maintenance_plans')->middleware('CheckRoles:maintenance_plans')->group(function () {
                Route::get('index', [MaintenancesController::class, 'index'])->name('maintenance_plans.index');
                Route::get('show/{planificacionMantenimiento}', [MaintenancesController::class, 'show'])->name('maintenance_plans.show');
                Route::post('store', [MaintenancesController::class, 'store'])->name('maintenance_plans.store');
                Route::delete('delete/{planificacionMantenimiento}', [MaintenancesController::class, 'delete'])->name('maintenance_plans.delete');
            });

            Route::prefix('ticket_admin')->middleware('CheckRoles:ticket_admin')->group(function () {
                Route::get('index', [TicketAdminController::class, 'index'])->name('ticket_admin.index');
                Route::get('data', [TicketAdminController::class, 'data'])->name('ticket_admin.data');
                Route::put('updatePriority/{ticket}', [TicketAdminController::class, 'updatePriority'])->name('ticket_admin.updatePriority');
                Route::put('updateType/{ticket}', [TicketAdminController::class, 'updateType'])->name('ticket_admin.updateType');
                Route::put('updateEncargado/{ticket}', [TicketAdminController::class, 'updateEncargado'])->name('ticket_admin.updateEncargado');
                Route::put('updateStatus/{ticket}', [TicketAdminController::class, 'updateStatus'])->name('ticket_admin.updateStatus');
                Route::put('update/{ticket}', [TicketAdminController::class, 'update'])->name('ticket_admin.update');
                Route::delete('delete/{ticket}', [TicketAdminController::class, 'delete'])->name('ticket_admin.delete');
            });

            Route::prefix('ticket_reports')->middleware('CheckRoles:ticket_reports')->group(function () {
                Route::get('index', [ReportesTicketsController::class, 'index'])->name('ticket_reports.index');
            });
        });
    });

    Route::prefix('api')->group(function () {
        Route::get('search/{model}', [SearchController::class, 'search']);
        Route::get('select/{model}/{search}/{extra_query_parameter?}', [SearchController::class, 'searchSelect']);

        Route::prefix('modelFilters')->middleware('CheckRoles:users')->group(function () {
            Route::get('getAvailableFilterByModel/{model}', [ModelFiltersController::class, 'getAvailableFilterByModel'])->name('api.modelFilters.getAvailableFilterByModel');
        });
    });

    Route::prefix('exports')->group(function () {
        Route::get('print', [PrintController::class, 'print'])->name('exports.print');
    });
});