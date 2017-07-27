<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\Support\GetSupport;

/**
 * The class retrieves subscribers for a list or lists for a subscriber.
 */
class ListSubscriber extends GetSupport
{
    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'ListSubscriber';
    }
}
