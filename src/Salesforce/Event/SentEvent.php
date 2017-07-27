<?php

namespace Salesforce\Event;

use Salesforce\Request\Soap\Support\GetSupport;

/**
 * Contains tracking data related to a send, including information on individual subscribers.
 */
class SentEvent extends GetSupport
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
        $this->obj = 'SentEvent';
        $this->getSinceLastBatch = true;
    }
}
