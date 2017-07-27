<?php

namespace Salesforce\Request\Rest;

use Salesforce\Client;
use Salesforce\Request\Request;
use Salesforce\Util\Util;

/**
 * This class represents the PATCH operation for REST service.
 */
class PatchRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $url         The endpoint URL
     * @param    array  $props       Dictionary type array which may hold e.g. array('id' => '', 'key' => '')
     */
    public function __construct($authStub, $url, $props)
    {
        $restResponse = Util::restPatch($url, json_encode($props), $authStub);
        parent::__construct($restResponse->body, $restResponse->httpcode, true);
    }
}
