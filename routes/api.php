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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => ['jwt.verify']], function() {
Route::apiResource('temporadas', 'TemporadaController');
Route::post('buscadorTemporada', 'TemporadaController@buscador');

Route::apiResource('candidatos', 'CandidatoController');
Route::post('buscarCandidatos', 'CandidatoController@buscarCandidatos');

Route::apiResource('socios', 'SocioController');
Route::post('buscarSocios', 'SocioController@buscador');


//DASHBOARD
Route::get('dashboard/{id}','DashboardController@index');
Route::get('temporadasDashboard','DashboardController@temporadas');
Route::get('sociosVotaron/export', 'DashboardController@export');
Route::post('dashboard-final/exportPdf', 'DashboardController@downloadPdf');
});

//Login
Route::post('login', 'UserController@login');
Route::post('logearVoto', 'VotacionController@logearVoto');

Route::post('comprobarTiempo', 'VotacionController@comprobarTiempo');

//VOTOS
Route::post('votacion', 'VotacionController@votar');
Route::post('candidatosTemporada', 'VotacionController@candidatosTemporada');

