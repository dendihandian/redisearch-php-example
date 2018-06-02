<?php

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

$router->get('products/suggestion', 'ProductsController@suggestion');
$router->get('products', 'ProductsController@index');
$router->post('products', 'ProductsController@store');
$router->get('products/{id}', 'ProductsController@show');
$router->patch('products/{id}', 'ProductsController@update');
$router->delete('products/{id}', 'ProductsController@destroy');
