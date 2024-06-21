<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/index', 'EgresadoController@index');
Route::get('/periodos', 'EgresadoController@getPeriodos');
Route::get('/anios_periodos', 'EgresadoController@getAnios_Periodos');
Route::get('/programas', 'EgresadoController@getProgramas');
Route::get('/egresados/{anio_periodo}/{idPrograma}', 'EgresadoController@getEgresados');
