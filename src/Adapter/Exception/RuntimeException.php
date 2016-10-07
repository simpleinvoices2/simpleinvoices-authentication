<?php
/**
 * @link      http://github.com/simpleinvoices2/simpleinvoices2
 * @copyright Copyright (c) 2016 Juan Pedro Gonzalez Gutierrez
 * @license   http://github.com/simpleinvoices2/simpleinvoices2/LICENSE GPL v3.0
 */

namespace Zend\Authentication\Adapter\DbTable\Exception;

use SimpleInvoices\Authentication\Exception;

class RuntimeException extends Exception\RuntimeException implements
    ExceptionInterface
{
}
