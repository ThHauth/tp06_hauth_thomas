<?php
use Slim\Factory\AppFactory;
use Doctrine\ORM\EntityManager;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
$routes = require __DIR__ . '/../router.php';

$container = new DI\Container();
$container->set(EntityManager::class, function($container) use ($entityManager) {
    return $entityManager;
});
AppFactory::setContainer($container);
$app = AppFactory::create();

$routes($app);


$app->run();

