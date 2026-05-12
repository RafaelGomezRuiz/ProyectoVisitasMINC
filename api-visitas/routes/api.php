<?php


use App\Http\Controllers\Api\Admin\UserController;

use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\LocalidadController;
use App\Http\Controllers\Api\PaisController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TipoVisitanteController;
use App\Http\Controllers\Api\VisitaController;
use App\Http\Controllers\API\VisitanteController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Http\Middleware\Authenticate as JwtAuthenticate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// rutas para el login
// 👇👇👇 AÑADE ESTA RUTA 👇👇👇
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
Route::get('admin/clientes-total', [VisitanteController::class, 'clientesTotal']);
// Rutas para el Panel de Administración
Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('visitantes/buscar', [VisitanteController::class, 'buscarPorDocumento']);
        Route::get('visitas/stats', [VisitaController::class, 'stats']);
        Route::apiResource('paises', PaisController::class);
        Route::apiResource('tipos-visitante', TipoVisitanteController::class);
        Route::apiResource('localidades', LocalidadController::class);
        Route::apiResource('horarios', HorarioController::class);
        Route::apiResource('areas', AreaController::class);
        Route::apiResource('visitantes', VisitanteController::class);
        Route::apiResource('reservas', ReservaController::class);
        Route::apiResource('visitas', VisitaController::class);
        Route::apiResource('roles', RoleController::class)->only(['index', 'show']);
        Route::apiResource('usuarios', UserController::class);
        


    });

    

});


