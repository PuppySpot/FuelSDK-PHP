<?php

namespace Salesforce\Request\Soap\Support;

use Salesforce\Request\Soap\InfoRequest;
use Salesforce\Request\Soap\BaseObject;
use Salesforce\Request\Soap\ContinueRequest;
use Salesforce\Request\Soap\GetRequest;

/**
 * This class represents the get operation for SOAP service.
 */
class GetSupport extends BaseObject
{
    /**
     * @return GetRequest     Object of type ET_Get which contains http status code, response, etc from the GET SOAP service
     */
    public function get()
    {
        $lastBatch = false;
        if (property_exists($this, 'getSinceLastBatch')) {
            $lastBatch = $this->getSinceLastBatch;
        }
        $response = new GetRequest($this->authStub, $this->obj, $this->props, $this->filter, $lastBatch);
        $this->lastRequestID = $response->request_id;

        return $response;
    }

    /**
     * @return ContinueRequest    returns more response from the SOAP service
     */
    public function getMoreResults()
    {
        $response = new ContinueRequest($this->authStub, $this->lastRequestID);
        $this->lastRequestID = $response->request_id;

        return $response;
    }

    /**
     * @return InfoRequest    returns information from the SOAP service
     */
    public function info()
    {
        return new InfoRequest($this->authStub, $this->obj);
    }
}
