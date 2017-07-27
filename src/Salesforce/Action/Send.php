<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * Used to send email and retrieve aggregate data based on a JobID.
 */
class Send extends CUDSupport
{
    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'Send';
    }
}
