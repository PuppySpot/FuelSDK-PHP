<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Request\Request;
use SoapVar;

/**
 * This class represents the DELETE operation for SOAP service.
 */
class DeleteRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $objType     Object name, e.g. "ImportDefinition", "DataExtension", etc
     * @param    array  $props       Dictionary type array which may hold e.g. array('id' => '', 'key' => '')
     */
    public function __construct($authStub, $objType, $props)
    {
        $authStub->refreshToken();
        $cr = [];
        $objects = [];

        $objects['Objects'] = new SoapVar($props, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');
        $objects['Options'] = '';
        $cr['DeleteRequest'] = $objects;

        $return = $authStub->__soapCall('Delete', $cr, null, null, $out_header);
        parent::__construct($return, $authStub->getLastResponseHTTPCode());

        if ($this->status) {
            if (property_exists($return, 'Results')) {
                // We always want the results property when doing a retrieve to be an array
                if (is_array($return->Results)) {
                    $this->results = $return->Results;
                } else {
                    $this->results = [$return->Results];
                }
            } else {
                $this->status = false;
            }
            if ($return->OverallStatus !== 'OK') {
                $this->status = false;
            }
        }
    }
}