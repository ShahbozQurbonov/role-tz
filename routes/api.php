<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);


    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create user');;
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete user');
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->middleware('role:admin');
    Route::post('/users/{user}/give-permission', [UserController::class, 'givePermission'])->middleware('role:admin');
    Route::delete('/users/{id}/remove-role/{role}', [UserController::class, 'removeRole'])->middleware('role:admin');
    Route::delete('/users/{id}/revoke-permission-to/{permission}', [UserController::class, 'revokePermissionTo'])->middleware('role:admin');
});


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
