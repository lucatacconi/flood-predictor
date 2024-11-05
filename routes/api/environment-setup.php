<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Ramsey\Uuid\Uuid;

use Crunz\Configuration\Configuration;
use Crunz\Schedule;
use Crunz\Filesystem;
use Crunz\Task\Collection;
use Crunz\Task\WrongTaskInstanceException;

foreach (glob(__DIR__ . '/../classes/*.php') as $filename){
    require_once $filename;
}

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

$app->group('/admin', function (RouteCollectorProxy $group) {

    $group->group('/environment-setup', function (RouteCollectorProxy $subGroup) {

        $subGroup->post('/db-preset', function (Request $request, Response $response, array $args) {

            $data = 'Alive';

            $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $response->withStatus(200)
                            ->withHeader("Content-Type", "application/json");
        });

    });
});
