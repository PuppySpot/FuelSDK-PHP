<?php

namespace Salesforce\Request\Rest;

use Salesforce\Client;
use Salesforce\Request\Request;
use Salesforce\Util\Util;

/**
 * This class represents the DELETE operation for REST service.
 */
class DeleteRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $url         The endpoint URL
     */
    public function __construct($authStub, $url)
    {
        $restResponse = Util::restDelete($url, $authStub);
        parent::__construct($restResponse->body, $restResponse->httpcode, true);
    }
}
