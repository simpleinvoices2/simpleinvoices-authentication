<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication\Adapter;

use Zend\EventManager\EventManagerInterface;
use SimpleInvoices\Authentication\AuthenticationEvent;

interface EventManagerAwareAdapterInterface
{
    /**
     * Inject an AuthenticationEvent instance.
     *
     * @return void
     */
    public function setEvent(AuthenticationEvent $event);
    
    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager);
}