<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';

if (!isset($app)) {
    throw new RuntimeException('A variável $app não foi inicializada. Verifique o arquivo app.php.');
}

// Incluindo os outros arquivos necessários
require_once __DIR__.'/Consulta_Patologia/index.php';
require_once __DIR__.'/Consultas/index.php';
// Continue incluindo os outros arquivos aqui...

$app->match('/', function () use ($app) {
    return $app['twig']->render('ag_dashboard.html.twig', array());
})->bind('dashboard');

$app->run();
