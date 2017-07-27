<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Request\Request;
use Salesforce\Util\Util;
use SoapVar;

/**
 * This class represents configurations required for SOAP operation.
 */
class ConfigureRequest extends Request
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
        $configure = [];
        $configureRequest = [];
        $configureRequest['Action'] = $action;
        $configureRequest['Configurations'] = [];

        if (!Util::isAssoc($props)) {
            foreach ($props as $value) {
                $configureRequest['Configurations'][] = new SoapVar($value, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');
            }
        } else {
            $configureRequest['Configurations'][] = new SoapVar($props, \SOAP_ENC_OBJECT, $objType, 'http://exacttarget.com/wsdl/partnerAPI');
        }

        $configure['ConfigureRequestMsg'] = $configureRequest;
        $return = $authStub->__soapCall('Configure', $configure, null, null, $out_header);
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
