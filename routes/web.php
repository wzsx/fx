<?php

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
$router->get('/key','User\UserController@key');
$router->post('/reg','User\UserController@reg');
$router->post('/login','User\UserController@login');
$router->post('/pub','Pub\PubController@pub');