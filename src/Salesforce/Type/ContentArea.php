<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * ET_ContentArea - Represents a ContentArea class.
 * A ContentArea represents a defined section of reusable content. One or many ContentAreas can be defined for an Email
 * object. A ContentArea is always acted upon in the context of an Email object.
 */
class ContentArea extends CUDSupport
{
    /**
     * @var int $folderId Gets or sets the folder identifier.
     */
    public $folderId;

    /**
     * Initializes a new instance of the class and will assign obj, folderProperty, folderMediaType property
     */
    public function __construct()
    {
        $this->obj = 'ContentArea';
        $this->folderProperty = 'CategoryID';
        $this->folderMediaType = 'content';
    }
}
