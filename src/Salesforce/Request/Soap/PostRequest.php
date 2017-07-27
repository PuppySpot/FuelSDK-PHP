<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Util\Util;
use Salesforce\Request\Request;
use SoapVar;

/**
 * This class represents the POST operation for SOAP service.
 */
class PostRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $objType     Object name, e.g. "ImportDefinition", "DataExtension", etc
     * @param    array  $props       Dictionary type array which may hold e.g. array('id' => '', 'key' => '')
     * @param    bool   $upsert      If true SaveAction is UpdateAdd, otherwise not. By default false.
     */
    public function __construct($authStub, $objType, $props, $upsert = false)
    {
        $authStub->refreshToken();
        $cr = [];
        $objects = [];

        if (Util::isAssoc($props)) {
            $objects['Objects'] = new SoapVar($props, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');
        } else {
            $objects['Objects'] = [];
            foreach ($props as $object) {
                $objects['Objects'][] = new SoapVar($object, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');
            }
        }

        if ($upsert) {
            $objects['Options'] = [
                'SaveOptions' => [
                    'SaveOption' => [
                        'PropertyName' => '*', 'SaveAction' => 'UpdateAdd',
                    ],
                ],
            ];
        } else {
            $objects['Options'] = '';
        }
        $cr['CreateRequest'] = $objects;
        $return = $authStub->__soapCall('Create', $cr, null, null, $out_header);
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
