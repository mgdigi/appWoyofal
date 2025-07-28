<?php

use App\Controller\AchatController;

$routes = [
     'POST:/api/achat' => [
        'controller' => AchatController::class,
        'method' => 'index',
    ],

    'GET:/api/achat/all' => [
        'controller' => AchatController::class,
        'method' => 'show',
    ],
   
    'GET:/api/achat/verifier/{numeroCompteur}' => [
        'controller' => AchatController::class,
        'method' => 'verifierCompteur',
    ],
    
];