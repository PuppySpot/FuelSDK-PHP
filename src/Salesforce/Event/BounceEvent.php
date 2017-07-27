<?php

namespace Salesforce\Event;

use Salesforce\Request\Soap\Support\GetSupport;

/**
 *    Contains information pertaining to the specific event of an email message bounce.
 */
class BounceEvent extends GetSupport
{
    /**
     * @var bool    Gets or sets a boolean value indicating whether to get since last batch. true if get since last
     *      batch; otherwise, false.
     */
    public $getSinceLastBatch;

    /**
     * Initializes a new instance of the class and set the since last batch to true.
     */
    public function __construct()
    {
        $this->obj = 'BounceEvent';
        $this->getSinceLastBatch = true;
    }
}
