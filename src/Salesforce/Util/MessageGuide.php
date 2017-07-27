<?php

namespace Salesforce\Util;

use Salesforce\Request\Rest\GetRequest;
use Salesforce\Request\Rest\PostRequest;
use Salesforce\Request\Rest\Support\CUDSupportRest;
use Salesforce\Request\Soap\PostRequest as SoapPostRequest;

/**
 * The class can get, convert, render, send messages.
 */
class MessageGuide extends CUDSupportRest
{
    /**
     * The constructor will assign endpoint, urlProps, urlPropsRequired fields of parent ET_BaseObjectRest
     */
    public function __construct()
    {
        $this->endpoint = 'https://www.exacttargetapis.com/guide/v1/messages/{id}';
        $this->urlProps = ['id'];
        $this->urlPropsRequired = [];
    }

    // method for calling a Fuel API using GET

    /**
     * @return GetRequest     Object of type ET_GetRest which contains http status code, response, etc from the GET
     *                        REST service
     */
    public function get()
    {
        $origEndpoint = $this->endpoint;
        $origProps = $this->urlProps;
        if (count($this->props) === 0) {
            $this->endpoint = 'https://www.exacttargetapis.com/guide/v1/messages/f:@all';
        } elseif (array_key_exists('key', $this->props)) {
            $this->endpoint = 'https://www.exacttargetapis.com/guide/v1/messages/key:{key}';
            $this->urlProps = ['key'];
        }
        $response = parent::get();
        $this->endpoint = $origEndpoint;
        $this->urlProps = $origProps;

        return $response;
    }

    // method for calling a Fuel API using POST

    /**
     * @return PostRequest     Object of type ET_PostRest which contains http status code, response, etc from the POST
     *                         REST service
     */
    public function convert()
    {
        $completeURL = 'https://www.exacttargetapis.com/guide/v1/messages/convert?access_token=' . $this->authStub->getAuthToken();

        return new PostRequest($this->authStub, $completeURL, $this->props);
    }

    // method for calling a Fuel API using POST

    /**
     * @return \Salesforce\Request\Rest\GetRequest|\Salesforce\Request\Rest\PostRequest|\Salesforce\Request\Soap\PostRequest
     */
    public function sendProcess()
    {
        $renderMG = new MessageGuide();
        $renderMG->authStub = $this->authStub;
        $renderMG->props = ['id' => $this->props['messageID']];
        $renderResult = $renderMG->render();
        if (!$renderResult->status) {
            return $renderResult;
        }

        $html = $renderResult->results->emailhtmlbody;
        $send = [];
        $send['Email'] = ['Subject' => $this->props['subject'], 'HTMLBody' => $html];
        $send['List'] = ['ID' => $this->props['listID']];

        return new SoapPostRequest($this->authStub, 'Send', $send);
    }

    // method for calling a Fuel API using GET or POST

    /**
     * @return GetRequest|PostRequest     Object of type ET_GetRest or ET_PostRest if props field is an array and holds
     *                                    id as a key
     */
    public function render()
    {
        $completeURL = null;
        $response = null;

        if (is_array($this->props) && array_key_exists('id', $this->props)) {
            $completeURL = "https://www.exacttargetapis.com/guide/v1/messages/render/{$this->props['id']}?access_token=" . $this->authStub->getAuthToken();
            $response = new GetRequest($this->authStub, $completeURL, null);
        } else {
            $completeURL = 'https://www.exacttargetapis.com/guide/v1/messages/render?access_token=' . $this->authStub->getAuthToken();
            $response = new PostRequest($this->authStub, $completeURL, $this->props);
        }

        return $response;
    }
}
