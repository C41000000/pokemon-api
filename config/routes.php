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

use Gokure\HyperfCors\CorsMiddleware;
use Hyperf\HttpServer\Router\Router;
use Hyperf\HttpServer\Annotation\Middleware;

Router::addGroup(
    '/pokemon', function () {
    Router::get('/list', [\App\Controller\Pokemon\PokemonController::class, 'list']);
},
    [
        'middleware' => [
            CorsMiddleware::class,
        ]
    ]
);
Router::get('/favicon.ico', function () {
    return '';
});
