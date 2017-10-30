<?php

namespace Salesforce\Type;

use Salesforce\Request\Soap\PerformRequest;
use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * This class represents an Automation.
 */
class Automation extends CUDSupport
{
    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'Automation';
    }

    /**
     * This method start this import process.
     *
     * @return PerformRequest     Object of type ET_Perform which contains http status code, response, etc from the Perform
     *                        SOAP service
     */
    public function start()
    {
        $response = new PerformRequest($this->authStub, $this->obj, 'start', $this->props);

        return $response;
    }
}
