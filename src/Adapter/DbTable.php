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

class DbTable extends AbstractAdapter
{
    /**
     * 
     * @var AdapterInterface
     */
    protected $adapter;
    
    /**
     * $authenticateResultInfo
     *
     * @var array
     */
    protected $authenticateResultInfo = null;
    
    /**
     * @var string
     */
    protected $table;
    
    public function __construct(AdapterInterface $adapter, $table = 'users')
    {
        $this->adapter = $adapter;
        $this->table   = $table;
    }
    
    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $dbSelect         = $this->authenticateCreateSelect();   
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
    
        $select = new Select( $this->table );
    
        // By email or username...
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            $select->where(['email' => $identity]);
        } else {
            $select->where(['username' => $identity]);
        }
    
        // Password and active user...
        $select->where([
            'password' => $this->getCredential(),
            'active'   => true,
        ]);
    
        return $select;
    }
    
    /**
     * This method accepts a Zend\Db\Sql\Select object and
     * performs a query against the database with that object.
     *
     * @param  Sql\Select $dbSelect
     * @throws Exception\RuntimeException when an invalid select object is encountered
     * @return array
     */
    protected function authenticateQuerySelect(Sql\Select $dbSelect)
    {
        $sql = new Sql\Sql($this->zendDb);
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
}