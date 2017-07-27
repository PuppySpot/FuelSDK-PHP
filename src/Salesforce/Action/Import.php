<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\GetRequest;
use Salesforce\Request\Soap\PerformRequest;
use Salesforce\Request\Soap\PostRequest;
use Salesforce\Request\Soap\Support\CUDSupport;
use SoapVar;

/**
 * This class defines a reusable pattern of import options.
 */
class Import extends CUDSupport
{
    /**
     * @var string|null     contains last import task ID if available
     */
    public $lastTaskID;

    /**
     * Initializes a new instance of the class and sets the obj property of parent.
     */
    public function __construct()
    {
        $this->obj = 'ImportDefinition';
    }

    /**
     * This method is used to create/post the instance
     *
     * @return PostRequest     Object of type ET_Post which contains http status code, response, etc from the POST SOAP
     *                     service
     */
    public function post()
    {
        $originalProp = $this->props;

        # If the ID property is specified for the destination then it must be a list import
        if (array_key_exists('DestinationObject', $this->props) && array_key_exists('ID', $this->props['DestinationObject'])) {
            $this->props['DestinationObject'] = new SoapVar($this->props['DestinationObject'], \SOAP_ENC_OBJECT, 'List', 'http://exacttarget.com/wsdl/partnerAPI');
        }

        $obj = parent::post();
        $this->props = $originalProp;

        return $obj;
    }

    /**
     * This method start this import process.
     *
     * @return PerformRequest     Object of type ET_Perform which contains http status code, response, etc from the Perform
     *                        SOAP service
     */
    public function start()
    {
        $originalProps = $this->props;
        $response = new PerformRequest($this->authStub, $this->obj, 'start', $this->props);
        if ($response->status) {
            $this->lastTaskID = $response->results[0]->Task->ID;
        }
        $this->props = $originalProps;

        return $response;
    }

    /**
     * This method is used to get Property Definition for a subscriber
     *
     * @return GetRequest     Object of type ET_Get which contains http status code, response, etc from the GET SOAP service
     */
    public function status()
    {
        $this->filter = [
            'Property'       => 'TaskResultID',
            'SimpleOperator' => 'equals',
            'Value'          => $this->lastTaskID,
        ];

        $response = new GetRequest(
            $this->authStub,
            'ImportResultsSummary',
            [
                'ImportDefinitionCustomerKey',
                'TaskResultID',
                'ImportStatus',
                'StartDate',
                'EndDate',
                'DestinationID',
                'NumberSuccessful',
                'NumberDuplicated',
                'NumberErrors',
                'TotalRows',
                'ImportType',
            ],
            $this->filter
        );

        $this->lastRequestID = $response->request_id;

        return $response;
    }
}
