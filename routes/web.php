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

$router->get('getstations', function () {
    return 'Hello World';
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


$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('users',  ['uses' => 'UserController@showAllUserss']);
  
    $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);
  
    $router->post('users', ['uses' => 'UserController@create']);
  
    $router->delete('users/{id}', ['uses' => 'UserController@delete']);
  
    $router->put('users/{id}', ['uses' => 'UserController@update']);
  });