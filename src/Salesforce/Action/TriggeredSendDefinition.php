<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\GetRequest;
use Salesforce\Request\Soap\PerformRequest;
use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * This class defines a triggered send in the account.
 */
class TriggeredSendDefinition extends CUDSupport
{
    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'TriggeredSendDefinition';
    }
}
