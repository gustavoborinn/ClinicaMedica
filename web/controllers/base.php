<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';


require_once __DIR__.'/Consulta_Patologia/index.php';
require_once __DIR__.'/Consultas/index.php';
require_once __DIR__.'/Contatos/index.php';
require_once __DIR__.'/Especialidade/index.php';
require_once __DIR__.'/Medicamentos/index.php';
require_once __DIR__.'/Medico_Especialidade/index.php';
require_once __DIR__.'/Medicos/index.php';
require_once __DIR__.'/Patologias/index.php';
require_once __DIR__.'/Pessoas/index.php';
require_once __DIR__.'/Tratamento/index.php';
require_once __DIR__.'/UF/index.php';

$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();