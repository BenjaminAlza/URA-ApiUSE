<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// EGRESADOS
Route::get('/periodos', 'EgresadoController@getPeriodos');
Route::get('/anios_periodos', 'EgresadoController@getAnios_Periodos');
Route::get('/programas', 'EgresadoController@getProgramas');

Route::get('/egresados/index', 'EgresadoController@index');
Route::get('/egresados/{anio_periodo}/{idprograma}', 'EgresadoController@getEgresados');

// MATRICULAS
Route::get('/matriculados/index', 'MatriculasController@index');
Route::get('/matriculados/{anio_periodo}/{idprograma}/{ciclo}', 'MatriculasController@getMatriculados');


// -- TOKENS --
Route::group(['middleware' => 'UDAToken'], function () {
    Route::get('/segundaEspecialidad', 'SegEspecialidadController@getSegEspecialidad');
    Route::get('/docentes', 'SegEspecialidadController@getDocentes');
});
// Route::get('/segundaEspecialidad', 'SegEspecialidadController@getSegEspecialidad');
// Route::get('/docentes', 'SegEspecialidadController@getDocentes');

