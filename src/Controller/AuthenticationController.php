<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace SimpleInvoices\Authentication\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SimpleInvoices\Authentication\Form\LoginForm;
use Zend\Authentication\AuthenticationServiceInterface;

class AuthenticationController extends AbstractActionController
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;
    
    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }
    
    public function loginAction()
    {
        //if already login, redirect to success page
        if ($this->authenticationService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }
        
        $form = new LoginForm();
        
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $this->authenticationService->getAdapter()
                     ->setIdentity($this->request->getPost('username'))
                     ->setCredential($this->request->getPost('password'));
                $result = $this->authenticationService->authenticate();
                if ($result->isValid()) {
                    return $this->redirect()->toRoute('home');
                }
            }
        }
         
        return new ViewModel([
            'form' => $form
        ]);
    }
    
    public function logoutAction()
    {
        //$this->getSessionStorage()->forgetMe();
        $this->authenticationService->clearIdentity();
         
        return $this->redirect()->toRoute('login');
    }
}
