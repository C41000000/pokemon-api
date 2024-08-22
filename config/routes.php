<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;
use App\Controller\Pokemon\PokemonController;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\Pokemon\PokemonController@list');
Router::addGroup("/pokemon", function (){
    Router::addRoute(['GET', 'POST'], '/list', 'App\Controller\Pokemon\PokemonController@list');
});
Router::get('/favicon.ico', function () {
    return '';
});
