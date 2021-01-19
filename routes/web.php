<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->get('/',  ['uses' => 'HelpController@showHelpMain']);
$router->get('help',  ['uses' => 'HelpController@showHelpMain']);
$router->get('/version', function () use ($router) {
    return $router->app->version()  ; 
});

$router->get('say/{id}', function ($id) {
    return 'say: '.$id;
});

$router->get('mock', function () {
    $table = [ 1,2 ];
    return $table;
});

/*$router->get('admin/profile', ['middleware' => 'auth', function () {
    //
}]);*/

//Users routes
$router->get('users',  ['uses' => 'UserController@showAllUsers']);

$router->get('user', ['uses' => 'HelpController@showHelpUser']);
$router->post('user', ['uses' => 'UserController@create']);
$router->delete('user/{id}', ['uses' => 'UserController@delete']);
$router->put('user/{id}', ['uses' => 'UserController@update']);
$router->get('user/{id}', ['uses' => 'UserController@showOneUser']);


// Messages routes
$router->get('messages',  ['uses' => 'MessageController@showAllMessages']);

$router->get('message', ['uses' => 'HelpController@showHelpMessage']);
$router->post('message', ['uses' => 'MessageController@create']);
$router->delete('message/{id}', ['uses' => 'MessageController@delete']);
$router->put('message/{id}', ['uses' => 'MessageController@update']);
$router->get('message/{id}', ['uses' => 'MessageController@showOneMessage']);

$router->post('file', ['uses' => 'FileController@new']);


$router->get('sys',  ['uses' => 'HelpController@showHelpSys']);

// system commands
$router->group(['prefix' => '/sys'], function () use ($router) {

    $router->get('run/{command}',  ['uses' => 'SystemController@exec_cli']);
    $router->get('help',  ['uses' => 'HelpController@showHelpSys']);
    $router->get('ls',  ['uses' => 'SystemController@getFiles']);
    $router->get('node',  ['uses' => 'SystemController@getNodename']);
    $router->get('queueerase',  ['uses' => 'SystemController@queueErase']);
    $router->get('spoollist',  ['uses' => 'SystemController@getSpoolList']);
    $router->get('getstations',  ['uses' => 'SystemController@getStations']);

});

