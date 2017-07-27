<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * ET_Email - Represents an email in a Marketing Cloud account.
 */
class Email extends CUDSupport
{
    /**
     * @var int    Gets or sets the folder identifier.
     */
    public $folderId;

    /**
     * Initializes a new instance of the class and will assign obj, folderProperty, folderMediaType property
     */
    public function __construct()
    {
        $this->obj = 'Email';
        $this->folderProperty = 'CategoryID';
        $this->folderMediaType = 'email';
    }
}
