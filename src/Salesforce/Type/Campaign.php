<?php

namespace Salesforce\Type;

use Salesforce\Request\Rest\Support\CUDSupportRest;

/**
 * Represents a program in an account
 */
class Campaign extends CUDSupportRest
{
    /**
     * Initializes a new instance of the class and will assign endpoint, urlProps, urlPropsRequired fields of parent
     * ET_BaseObjectRest
     */
    public function __construct()
    {
        $this->endpoint = 'https://www.exacttargetapis.com/hub/v1/campaigns/{id}';
        $this->urlProps = ['id'];
        $this->urlPropsRequired = [];
    }
}
