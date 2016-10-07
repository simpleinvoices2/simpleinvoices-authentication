<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */
namespace SimpleInvoices\Authentication\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('login');
        $this->setAttribute('class', 'login-form');

        $this->add([
            'name' => 'username',
            'type' => 'Text',
            'options' => [
                'label' => 'Username',
                'label_attributes' => [
                    'class'  => 'sr-only',
                ],
            ],
            'attributes' => [
                'placeholder' => 'Username...',
                'class'       => 'form-control',
                'required'    => 'required',
            ]
        ]);
        $this->add([
            'name' => 'password',
            'type' => 'Password',
            'options' => [
                'label' => 'Password',
                'label_attributes' => [
                    'class'  => 'sr-only',
                ],
            ],
            'attributes' => [
                'placeholder' => 'Password...',
                'class'    => 'form-control',
                'required' => 'required',
            ]
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ],
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'value' => 'Sign In',
                'id'    => 'signin',
                'class' => 'btn btn-lg btn-block',
            ],
        ]);
    }
}