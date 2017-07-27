<?php

namespace Salesforce\Request\Rest;

use Salesforce\Client;
use Salesforce\Request\Request;
use Salesforce\Util\Util;

/**
 * This class represents the GET operation for REST service.
 */
class GetRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub    The ET client object which performs the auth token, refresh token using clientID
     *                               clientSecret
     * @param    string $url         The endpoint URL
     * @param    mixed  $qs          Reserved for future use
     */
    public function __construct($authStub, $url, $qs = null)
    {
        $restResponse = Util::restGet($url, $authStub);
        $this->moreResults = false;
        parent::__construct($restResponse->body, $restResponse->httpcode, true);
    }
}
