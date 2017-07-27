<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * This class represents an Account.
 */
class Organization extends CUDSupport
{
    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'Account';
    }
}
