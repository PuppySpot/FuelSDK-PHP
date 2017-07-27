<?php

namespace Salesforce\DataExtension;

use Salesforce\Request\Soap\GetRequest;
use Salesforce\Request\Soap\Support\GetSupport;

/**
 * ETDataExtensionColumn - Represents Data Extension Field.
 */
class Column extends GetSupport
{
    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'DataExtensionField';
        $this->folderProperty = 'CategoryID';
        $this->folderMediaType = 'dataextension';
    }

    /**
     * Get this instance.
     *
     * @return GetRequest     Object of type ET_Get which contains http status code, response, etc from the GET SOAP service
     */
    public function get()
    {
        $fixCustomerKey = false;

        if ($this->filter && array_key_exists('Property', $this->filter) && $this->filter['Property'] === 'CustomerKey') {
            $this->filter['Property'] = 'DataExtension.CustomerKey';
            $fixCustomerKey = true;
        }
        $response = parent::get();
        if ($fixCustomerKey) {
            $this->filter['Property'] = 'CustomerKey';
        }

        return $response;
    }
}
