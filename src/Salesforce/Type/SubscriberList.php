<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\Support\CUDWithUpsertSupport;

/**
 * This class represents a marketing list of subscribers.
 */
class SubscriberList extends CUDWithUpsertSupport
{
    /**
     * @var int        Gets or sets the folder identifier.
     */
    public $folderId;

    /**
     * Initializes a new instance of the class and set the property obj, folderProperty and folderMediaType to
     * appropriate values.
     */
    public function __construct()
    {
        $this->obj = 'List';
        $this->folderProperty = 'Category';
        $this->folderMediaType = 'list';
    }
}
