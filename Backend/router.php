<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Controllers\UserController;
use App\Middlewares\CorsMiddleware;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response;
    });

    $app->add(CorsMiddleware::class);

    $app->group('/users', function (Group $group) {
        
        $group->post('/connexion', UserController::class . ":connexion");
        $group->post('/createUser', UserController::class . ":addUser");
    });


    $options = [
        "attribute" => "token",
        "header" => "Authorization",
        "regexp" => "/Bearer\s+(.*)$/i",
        "secure" => false,
        "algorithm" => ["HS256"],
        "secret" => "makey1234567",
        "path" => ["/"],
        "ignore" => ["/users/createUser","/users/connexion"],
        "error" => function ($response, $arguments) {
            $data = array('ERREUR' => 'Connexion', 'ERREUR' => 'JWT Non valide');
            $response = $response->withStatus(401);
            return $response->withHeader("Content-Type", "application/json")->getBody()->write(json_encode($data));
        }
    ];

    $app->add(new JwtAuthentication($options));
};