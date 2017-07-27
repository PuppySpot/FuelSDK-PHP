<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\PostRequest;
use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * Defines a triggered send in the account.
 */
class TriggeredSend extends CUDSupport
{
    /**
     * @var array            Gets or sets the subscribers. e.g. array("EmailAddress" => "", "SubscriberKey" => "")
     */
    public $subscribers;

    /**
     * @var int            Gets or sets the folder identifier.
     */
    public $folderId;

    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'TriggeredSendDefinition';
        $this->folderProperty = 'CategoryID';
        $this->folderMediaType = 'triggered_send';
    }

    /**
     * Send this instance.
     *
     * @return PostRequest     Object of type ET_Post which contains http status code, response, etc from the POST SOAP
     *                     service
     */
    public function Send()
    {
        return new PostRequest(
            $this->authStub, 'TriggeredSend', [
            'TriggeredSendDefinition' => $this->props,
            'Subscribers'             => $this->subscribers,
        ]
        );
    }
}
