<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Request\Request;

/**
 * This class represents ContinueRequest for SOAP operation.
 */
class ContinueRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub      The ET client object which performs the auth token, refresh token using clientID
     *                                 clientSecret
     * @param    string $request_id    The request ID from the SOAP response
     */
    public function __construct($authStub, $request_id)
    {
        $authStub->refreshToken();
        $rrm = [];
        $request = [];
        $retrieveRequest = [];

        $retrieveRequest['ContinueRequest'] = $request_id;
        $retrieveRequest['ObjectType'] = null;

        $request['RetrieveRequest'] = $retrieveRequest;
        $rrm['RetrieveRequestMsg'] = $request;

        $return = $authStub->__soapCall('Retrieve', $rrm, null, null, $out_header);
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
                $this->results = [];
            }

            $this->moreResults = false;

            if ($return->OverallStatus === 'MoreDataAvailable') {
                $this->moreResults = true;
            }

            if ($return->OverallStatus !== 'OK' && $return->OverallStatus !== 'MoreDataAvailable') {
                $this->status = false;
                $this->message = $return->OverallStatus;
            }

            $this->request_id = $return->RequestID;
        }
    }
}