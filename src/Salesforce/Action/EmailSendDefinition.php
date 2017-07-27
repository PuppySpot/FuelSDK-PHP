<?php

namespace Salesforce\Action;

use Salesforce\Request\Soap\GetRequest;
use Salesforce\Request\Soap\PerformRequest;
use Salesforce\Request\Soap\Support\CUDSupport;

/**
 * This class contains the message information, sender profile, delivery profile, and audience information.
 */
class EmailSendDefinition extends CUDSupport
{
    /**
     * @var int            Gets or sets the folder identifier.
     */
    public $folderId;

    /**
     * @var string|null    contains last task ID if available
     */
    public $lastTaskID;

    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'EmailSendDefinition';
        $this->folderProperty = 'CategoryID';
        $this->folderMediaType = 'userinitiatedsends';
    }

    /**
     * Send this instance.
     *
     * @return PerformRequest     Object of type ET_Perform which contains http status code, response, etc from the START
     *                        SOAP service
     */
    public function send()
    {
        $originalProps = $this->props;
        $response = new PerformRequest($this->authStub, $this->obj, 'start', $this->props);
        if ($response->status) {
            $this->lastTaskID = $response->results[0]->Task->ID;
        }
        $this->props = $originalProps;

        return $response;
    }

    /**
     * Status of this instance.
     *
     * @return GetRequest     Object of type ET_Get which contains http status code, response, etc from the GET SOAP service
     */
    public function status()
    {
        $this->filter = ['Property' => 'ID', 'SimpleOperator' => 'equals', 'Value' => $this->lastTaskID];
        $response = new GetRequest(
            $this->authStub,
            'Send',
            [
                'ID',
                'CreatedDate',
                'ModifiedDate',
                'Client.ID',
                'Email.ID',
                'SendDate',
                'FromAddress',
                'FromName',
                'Duplicates',
                'InvalidAddresses',
                'ExistingUndeliverables',
                'ExistingUnsubscribes',
                'HardBounces',
                'SoftBounces',
                'OtherBounces',
                'ForwardedEmails',
                'UniqueClicks',
                'UniqueOpens',
                'NumberSent',
                'NumberDelivered',
                'NumberTargeted',
                'NumberErrored',
                'NumberExcluded',
                'Unsubscribes',
                'MissingAddresses',
                'Subject',
                'PreviewURL',
                'SentDate',
                'EmailName',
                'Status',
                'IsMultipart',
                'SendLimit',
                'SendWindowOpen',
                'SendWindowClose',
                'BCCEmail',
                'EmailSendDefinition.ObjectID',
                'EmailSendDefinition.CustomerKey',
            ],
            $this->filter
        );

        $this->lastRequestID = $response->request_id;

        return $response;
    }
}
