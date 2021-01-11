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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/*$router->post('foo', function () {
    //
});
*/

$router->get('say/{id}', function ($id) {
    return 'say: '.$id;
});

$router->get('user/{id}', 'UserController@show');

/*
example:
 $router->get($uri, $callback);
 $router->post($uri, $callback);
 $router->put($uri, $callback);
 $router->patch($uri, $callback);
 $router->delete($uri, $callback);
 $router->options($uri, $callback);
*/

$router->get('admin/profile', ['middleware' => 'auth', function () {
    //
}]);


$router->group(['prefix' => '/'], function () use ($router) {
    $router->get('users',  ['uses' => 'UserController@showAllUserss']);
    $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);
    $router->post('users', ['uses' => 'UserController@create']);
    $router->delete('users/{id}', ['uses' => 'UserController@delete']);
    $router->put('users/{id}', ['uses' => 'UserController@update']);

    $router->get('help',  ['uses' => 'HelpController@showHelp']);
    $router->get('ls',  ['uses' => 'SystemController@exec_cli']);
    $router->get('node',  ['uses' => 'SystemController@getNodemane']);
    $router->get('run/{command}',  ['uses' => 'SystemController@exec_cli']);

    // system commands
    $router->group(['prefix' => '/sys'], function () use ($router) {
        $router->get('help',  ['uses' => 'HelpController@showHelp']);
        $router->get('ls',  ['uses' => 'SystemController@exec_cli']);
        $router->get('node',  ['uses' => 'SystemController@getNodename']);
        $router->get('queueerase',  ['uses' => 'SystemController@queueErase']);
        $router->get('spoollist',  ['uses' => 'SystemController@getSpoolList']);
        $router->get('getstations',  ['uses' => 'SystemController@getStations']);

    });
});

