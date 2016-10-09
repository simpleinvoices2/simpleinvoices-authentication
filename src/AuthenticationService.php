<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication;

use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;
use Zend\EventManager\EventManagerInterface;
use Zend\Authentication\AuthenticationService as BaseAuthenticationService;

class AuthenticationService extends BaseAuthenticationService
{
    /**
     * @var EventManagerInterface
     */
    protected $events = null;
    
    /**
     * Constructor
     *
     * @param  Storage\StorageInterface $storage
     * @param  Adapter\AdapterInterface $adapter
     */
    public function __construct(Storage\StorageInterface $storage = null, Adapter\AdapterInterface $adapter = null, EventManagerInterface $events = null)
    {
        parent::__construct($storage, $adapter);
        
        if ($events instanceof EventManagerInterface) {
            $this->events = $events;
        }
    }
    
    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(Adapter\AdapterInterface $adapter = null)
    {
        if (!$adapter) {
            if (!$adapter = $this->getAdapter()) {
                throw new Exception\RuntimeException('An adapter must be set or passed prior to calling authenticate()');
            }
        }
        
        // trigger event to allow customization
        if ($this->events instanceof EventManagerInterface) {
            $event = new AuthenticationEvent();
            $event->setName(AuthenticationEvent::EVENT_AUTHENTICATE);
            $event->setTarget($this);
            $event->setAdapter($adapter);
            $this->events->triggerEvent($event);
        }
        
        $result = parent::authenticate($adapter);
    
        // trigger event to allow customization
        if ($this->events instanceof EventManagerInterface) {
            $event = new AuthenticationEvent();
            $event->setTarget($this);
            $event->setAdapter($adapter);
            
            if ($result->isValid()) {
                $event->setName(AuthenticationEvent::EVENT_AUTHENTICATE_SUCCESS);    
                $event->setParam('authenticate_result', $result);
            } else {
                $event->setName(AuthenticationEvent::EVENT_AUTHENTICATE_ERROR);
            }
            
            $this->events->triggerEvent($event);
        }
        
        return $result;
    }
}