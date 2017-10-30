<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Request\Request;
use SoapVar;

/**
 * This class represents the PERFORM operation for SOAP service.
 */
class PerformRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $objType     Object name, e.g. "ImportDefinition", "DataExtension", etc
     * @param    string $action      Action names e.g. "create", "delete", "update", etc
     * @param    array  $props       Dictionary type array which may hold e.g. array('id' => '', 'key' => '')
     */
    public function __construct($authStub, $objType, $action, $props)
    {
        $authStub->refreshToken();
        $perform = [];
        $performRequest = [];
        $performRequest['Action'] = $action;
        $performRequest['Definitions'] = [];
        $performRequest['Definitions'][] = new SoapVar($props, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');

        $perform['PerformRequestMsg'] = $performRequest;
        $return = $authStub->__soapCall('Perform', $perform, null, null, $out_header);
        parent::__construct($return, $authStub->getLastResponseHTTPCode());
        if ($this->status) {
            if (property_exists($return->Results, 'Result')) {
                if (is_array($return->Results->Result)) {
                    $this->results = $return->Results->Result;
                } else {
                    $this->results = [$return->Results->Result];
                }
                if ($return->OverallStatus !== 'OK') {
                    $this->status = false;
                }
            } else {
                $this->status = false;
            }
        }
    }
}