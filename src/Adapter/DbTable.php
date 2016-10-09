<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication\Adapter;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\EventManager\EventManagerInterface;
use SimpleInvoices\Authentication\AuthenticationEvent;

class DbTable extends AbstractAdapter implements EventManagerAwareAdapterInterface
{
    /**
     * 
     * @var AdapterInterface
     */
    protected $adapter;
    
    /**
     * @var AuthenticationEvent
     */
    protected $event;
    
    /**
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * $authenticateResultInfo
     *
     * @var array
     */
    protected $authenticateResultInfo = null;
    
    /**
     * @var string
     */
    protected $tableName;
    
    public function __construct(AdapterInterface $adapter, $table = 'users')
    {
        $this->adapter     = $adapter;
        $this->tableName   = $table;
    }
    
    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $this->authenticateSetup();
        $dbSelect         = $this->authenticateCreateSelect();
        
        // trigger an event to allow customization of the SQL query
        if ($this->events instanceof EventManagerInterface) {
            $event = clone $this->event;
            $event->setName(AuthenticationEvent::EVENT_AUTHENTICATE_SQL);
            $event->setParam('select', $dbSelect);
            $this->events->triggerEvent($event);
        }
        
        $resultIdentities = $this->authenticateQuerySelect($dbSelect);
        
        if (($authResult = $this->authenticateValidateResultSet($resultIdentities)) instanceof AuthenticationResult) {
            return $authResult;
        }
        
        // At this point, ambiguity is already done. Loop, check and break on success.
        foreach ($resultIdentities as $identity) {
            $authResult = $this->authenticateValidateResult($identity);
            if ($authResult->isValid()) {
                break;
            }
        }
        
        return $authResult;
    }
    
    /**
     * Creates a Zend\Authentication\Result object from the information that
     * has been collected during the authenticate() attempt.
     *
     * @return AuthenticationResult
     */
    protected function authenticateCreateAuthResult()
    {
        return new AuthenticationResult(
            $this->authenticateResultInfo['code'],
            $this->authenticateResultInfo['identity'],
            $this->authenticateResultInfo['messages']
            );
    }
    
    /**
     * This method creates a Zend\Db\Sql\Select object that
     * is completely configured to be queried against the database.
     *
     * @return Sql\Select
     */
    protected function authenticateCreateSelect()
    {
        $identity = $this->getIdentity();
    
        $select = new Select( $this->tableName );
    
        // By email or username...
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            $select->where(['email' => $identity]);
        } else {
            $select->where(['username' => $identity]);
        }
    
        // active user...
        $select->where([
            'active'   => true,
        ]);
    
        // NOTE: Password is not set as we have to use 'password_verify'.
        
        return $select;
    }
    
    /**
     * This method accepts a Zend\Db\Sql\Select object and
     * performs a query against the database with that object.
     *
     * @param  Select $dbSelect
     * @throws Exception\RuntimeException when an invalid select object is encountered
     * @return array
     */
    protected function authenticateQuerySelect(Select $dbSelect)
    {
        $sql = new Sql($this->adapter);
        $statement = $sql->prepareStatementForSqlObject($dbSelect);
        try {
            $result = $statement->execute();
            $resultIdentities = [];
            // iterate result, most cross platform way
            foreach ($result as $row) {
                // ZF-6428 - account for db engines that by default return uppercase column names
                if (isset($row['ZEND_AUTH_CREDENTIAL_MATCH'])) {
                    $row['zend_auth_credential_match'] = $row['ZEND_AUTH_CREDENTIAL_MATCH'];
                    unset($row['ZEND_AUTH_CREDENTIAL_MATCH']);
                }
                
                // Verify the password
                $resultIdentities[] = $row;
            }
        } catch (\Exception $e) {
            throw new Exception\RuntimeException(
                'The supplied parameters to DbTable failed to '
                . 'produce a valid sql statement, please check table and column names '
                . 'for validity.',
                0,
                $e
            );
        }
        return $resultIdentities;
    }
    
    /**
     * This method abstracts the steps involved with making sure that 
     * this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws Exception\RuntimeException in the event that setup was not done properly
     * @return bool
     */
    protected function authenticateSetup()
    {
        $exception = null;
    
        if ($this->tableName == '') {
            $exception = 'A table must be supplied for the DbTable authentication adapter.';
        } elseif ($this->identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with DbTable.';
        } elseif ($this->credential === null) {
            $exception = 'A credential value was not provided prior to authentication with DbTable.';
        }
    
        if (null !== $exception) {
            throw new Exception\RuntimeException($exception);
        }
    
        $this->authenticateResultInfo = [
            'code'     => AuthenticationResult::FAILURE,
            'identity' => $this->identity,
            'messages' => []
        ];
    
        return true;
    }
    
    /**
     * This method attempts to validate that the record in the resultset is 
     * indeed a record that matched the identity provided to this adapter.
     *
     * @param  array $resultIdentity
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        if (!password_verify($this->credential, $resultIdentity['password'])) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }
        
        $this->resultRow = $resultIdentity;
        
        $this->authenticateResultInfo['code']       = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }
    
    /**
     * This method attempts to make certain that only one record 
     * was returned in the resultset
     *
     * @param  array $resultIdentities
     * @return bool|\Zend\Authentication\Result
     */
    protected function authenticateValidateResultSet(array $resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1 && false === $this->getAmbiguityIdentity()) {
            $this->authenticateResultInfo['code']       = AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->authenticateCreateAuthResult();
        }
    
        return true;
    }
    
    /**
     * Inject an AuthenticationEvent instance.
     * 
     * @return DbTable
     */
    public function setEvent(AuthenticationEvent $event)
    {
        $this->event = $event;
        return $this;
    }
    
    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return DbTable
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
        return $this;
    }
}