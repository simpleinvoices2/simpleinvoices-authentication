<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractController;
use Zend\EventManager\EventManagerInterface;

class AuthenticationListener extends AbstractListenerAggregate
{

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'initSession'], 9999);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'authentication'], -50);
        
        // Change layout if not authenticated.
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'setLayout'], 1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'setLayout'], 1);
    }
    
    /**
     * Listen to the 'route' event and check for authentication.
     * 
     * @param MvcEvent $event
     * @return void|\Zend\Stdlib\ResponseInterface
     */
    public function authentication(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        
        if (! $match) {
            // We cannot do anything without a resolved route.
            return;
        }
        
        if ($match->getMatchedRouteName() === 'login') {
            // login is allowed always
            return;
        }
        
        // Get AuthenticationService and do the verification.
        $services    = $event->getApplication()->getServiceManager();
        $authService = $services->get('SimpleInvoices\AuthenticationService');
        
        // If user has an identity.
        if ($authService->hasIdentity()) {
            return;
        }
        
        // Redirect to the user login page
        $router   = $event->getRouter();
        $url      = $router->assemble([], ['name' => 'login']);
        $response = $event->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        
        return $response;
    }
    
    /**
     * Listen to the "dispatch" and "dispatch.error" events and determine which
     * layout should be used.
     *
     * If the user is not authenticated we load the empty layout.
     *
     * @param  MvcEvent $event
     * @return void
     */
    public function setLayout(MvcEvent $event)
    {
        // Get AuthenticationService and do the verification.
        $services    = $event->getApplication()->getServiceManager();
        $authService = $services->get('SimpleInvoices\AuthenticationService');
        
        if ($authService->hasIdentity()) {
            return;
        }
        
        // We are not authenticated, therefore load the empty layout
        $controller = $event->getTarget();
        if ($controller instanceof AbstractController) {
            $controller->layout('layout/empty');
            return;
        }
        
        $viewModel = $event->getViewModel();
        $viewModel->setTemplate('layout/empty');
        
    }
    
}