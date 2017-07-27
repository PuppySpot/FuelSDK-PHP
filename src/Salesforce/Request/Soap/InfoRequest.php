<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Request\Request;

/**
 * This class Provides info about a object like Campaign, Asset, DataExtension, etc.
 */
class InfoRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $objType     Object name, e.g. "ImportDefinition", "DataExtension", etc
     * @param    bool   $extended    If true extended properties will be stored in the results, else regular properties
     *                               will be stored. By default false.
     */
    public function __construct($authStub, $objType, $extended = false)
    {
        $authStub->refreshToken();
        $drm = [];
        $request = [];
        $describeRequest = [];

        $describeRequest['ObjectDefinitionRequest'] = ['ObjectType' => $objType];

        $request['DescribeRequests'] = $describeRequest;
        $drm['DefinitionRequestMsg'] = $request;

        $return = $authStub->__soapCall('Describe', $drm, null, null, $out_header);
        parent::__construct($return, $authStub->getLastResponseHTTPCode());

        if ($this->status) {
            if (property_exists($return->ObjectDefinition, 'Properties')) {
                if ($extended) {
                    $this->results = $return->ObjectDefinition->ExtendedProperties->ExtendedProperty;
                } else {
                    $this->results = $return->ObjectDefinition->Properties;
                }
            } else {
                $this->status = false;
            }
        }
    }
}
