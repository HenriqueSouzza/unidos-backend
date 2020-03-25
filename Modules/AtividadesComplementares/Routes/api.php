<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/atividadescomplementares', function (Request $request) {
    return $request->user();
});


// Route::group(['middleware' => ['auth:api' /*, 'check.user.acl'*/]], function(){
    
    Route::get('atividadescomplementares/grupos-atividades','GruposAtividadesController@index');

    Route::get('atividadescomplementares/alunos-inscritos','AlunosInscritosController@index');

// });