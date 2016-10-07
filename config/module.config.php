<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication;

use Zend\Router\Http\Literal;
use Zend\Session\Storage\SessionArrayStorage;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/login.html',
                    'defaults' => [
                        'controller' => Controller\AuthenticationController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/logout.html',
                    'defaults' => [
                        'controller' => Controller\AuthenticationController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AuthenticationController::class => Service\AuthenticationControllerFactory::class,
        ],
    ],
    'listeners' => [
        Listener\AuthenticationListener::class,
    ],
    'session_config' => [
        'remember_me_seconds' => 60*60*1,  // Session will expire in 1 hour.
        'name'                => 'si',     // Session name.
    ],
    'session_storage' => [
        'type' => SessionArrayStorage::class,
    ],
    'service_manager' => [
        'factories' => [
            'SimpleInvoices\AuthenticationService' => Service\AuthenticationServiceFactory::class,
            Listener\AuthenticationListener::class => Service\AuthenticationListenerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'layout/empty'           => __DIR__ . '/../view/layout/empty.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];