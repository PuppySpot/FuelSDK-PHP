<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\Support\CUDWithUpsertSupport;

/**
 * A person subscribed to receive email or SMS communication.
 */
class Subscriber extends CUDWithUpsertSupport
{
    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'Subscriber';
    }
}
