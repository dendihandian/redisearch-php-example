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

$router->group(['prefix' => 'products'], function ($router) {
    $router->get('suggestion', 'ProductsController@suggestion');

    $router->get('/', 'ProductsController@index');
    $router->post('/', 'ProductsController@store');
    $router->get('/{id}', 'ProductsController@show');
    $router->patch('/{id}', 'ProductsController@update');
    $router->delete('/{id}', 'ProductsController@destroy');

    $router->group(['prefix' => 'aggregations'], function ($router) {
        $router->get('/average/{field}', 'ProductsController@average');
    });
});
