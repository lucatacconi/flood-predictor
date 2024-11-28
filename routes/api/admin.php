<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Ramsey\Uuid\Uuid;

foreach (glob(__DIR__ . '/../classes/*.php') as $filename){
    require_once $filename;
}

$app->group('/admin', function (RouteCollectorProxy $group) {

    $group->get('/alive', function (Request $request, Response $response, array $args) {

        $data = 'Alive';

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json");
    });

});
