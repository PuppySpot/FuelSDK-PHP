<?php

namespace Salesforce\Event;

use Salesforce\Request\Soap\Support\GetSupport;

/**
 * Contains information regarding a specific un-subscription action taken by a subscriber.
 */
class UnsubEvent extends GetSupport
{
    /**
     * @var bool    Gets or sets a boolean value indicating whether this object get since last batch. true if get since
     *      last batch; otherwise, false.
     */
    public $getSinceLastBatch;

    /**
     * Initializes a new instance of the class and set the since last batch to true.
     */
    public function __construct()
    {
        $this->obj = 'UnsubEvent';
        $this->getSinceLastBatch = true;
    }
}
