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

class AuthenticationController extends AbstractActionController
{
    public function loginAction()
    {
        $form = new LoginForm();
        
        return new ViewModel([
            'form' => $form
        ]);
    }
    
    public function logoutAction()
    {
        return new ViewModel();
    }
}
