<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\Storage\Session;
use Zend\Db\Adapter\AdapterInterface;
use SimpleInvoices\Authentication\Adapter\DbTable;
use SimpleInvoices\Authentication\AuthenticationService;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $adapter = $container->get(AdapterInterface::class);
        $dbTable = new DbTable($adapter, 'users'); // 'si_user', 'email', 'password', 'MD5(?) AND enabled=1');
        $storage = new Session('si');
        
        $eventManager = null;
        if ($container->has('SimpleInvoices\EventManager')) {
            $eventManager = $container->get('SimpleInvoices\EventManager');
        } elseif($container->has('EventManager')) {
            $eventManager = $container->get('EventManager');
        }
        
        return new AuthenticationService($storage, $dbTable, $eventManager);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, 'SimpleInvoice\AuthenticationService');
    }
}
