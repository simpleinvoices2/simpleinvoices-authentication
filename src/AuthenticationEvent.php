<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\EventManager\Event;

class AuthenticationEvent extends Event
{
    const EVENT_AUTHENTICATE         = 'siAuth.authenticate';
    const EVENT_AUTHENTICATE_SUCCESS = 'siAuth.authenticate.success';
    const EVENT_AUTHENTICATE_ERROR   = 'siAuth.authenticate.error';
    
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    
    /**
     * Returns the authentication adapter.
     * 
     * @return \Zend\Authentication\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Sets the authentication adapter.
     * 
     * @param AdapterInterface $adapter
     * @return AuthenticationEvent
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->setParam('authenticate_adapter', $adapter);
        $this->adapter = $adapter;
        return $this;
    }
    
}