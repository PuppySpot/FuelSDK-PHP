<?php

namespace Salesforce\DataExtension;

use Exception;
use Salesforce\Request\Soap\DeleteRequest;
use Salesforce\Request\Soap\GetRequest;
use Salesforce\Request\Soap\PatchRequest;
use Salesforce\Request\Soap\PostRequest;
use Salesforce\Request\Soap\Support\CUDWithUpsertSupport;

/**
 * ETDataExtensionRow - Represents Data Extension Row.
 */
class Row extends CUDWithUpsertSupport
{
    /**
     * @var string            Gets or sets the name of the data extension.
     */
    public $Name;

    /**
     * @var string            Gets or sets the data extension customer key.
     */
    public $CustomerKey;

    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'DataExtensionObject';
    }

    /**
     * Get this instance.
     *
     * @return GetRequest     Object of type ET_Get which contains http status code, response, etc from the GET SOAP service
     */
    public function get()
    {
        $this->getName();
        $this->obj = 'DataExtensionObject[' . $this->Name . ']';
        $response = parent::get();
        $this->obj = 'DataExtensionObject';

        return $response;
    }

    private function getName()
    {
        if (null === $this->Name) {
            if (null === $this->CustomerKey) {
                throw new Exception('Unable to process request due to CustomerKey and Name not being defined on ET_DataExtension_Row');
            }

            $nameLookup = new DataExtension();
            $nameLookup->authStub = $this->authStub;
            $nameLookup->props = ['Name', 'CustomerKey'];
            $nameLookup->filter = [
                'Property' => 'CustomerKey', 'SimpleOperator' => 'equals', 'Value' => $this->CustomerKey,
            ];
            $nameLookupGet = $nameLookup->get();
            if ($nameLookupGet->status && count($nameLookupGet->results) == 1) {
                $this->Name = $nameLookupGet->results[0]->Name;
            } else {
                throw new Exception('Unable to process request due to unable to find DataExtension based on CustomerKey');
            }
        }
    }

    /**
     * Post this instance.
     *
     * @return PostRequest     Object of type ET_Post which contains http status code, response, etc from the POST SOAP
     *                     service
     */
    public function post()
    {
        $this->getCustomerKey();
        $originalProps = $this->props;
        $overrideProps = [];
        $fields = [];

        foreach ($this->props as $key => $value) {
            $fields[] = ['Name' => $key, 'Value' => $value];
        }
        $overrideProps['CustomerKey'] = $this->CustomerKey;
        $overrideProps['Properties'] = ['Property' => $fields];

        $this->props = $overrideProps;
        $response = parent::post();
        $this->props = $originalProps;

        return $response;
    }

    private function getCustomerKey()
    {
        if (null === $this->CustomerKey) {
            if (null === $this->Name) {
                throw new Exception('Unable to process request due to CustomerKey and Name not being defined on ET_DataExtension_Row');
            }

            $nameLookup = new DataExtension();
            $nameLookup->authStub = $this->authStub;
            $nameLookup->props = ['Name', 'CustomerKey'];
            $nameLookup->filter = ['Property' => 'Name', 'SimpleOperator' => 'equals', 'Value' => $this->Name];
            $nameLookupGet = $nameLookup->get();
            if ($nameLookupGet->status && count($nameLookupGet->results) == 1) {
                $this->CustomerKey = $nameLookupGet->results[0]->CustomerKey;
            } else {
                throw new Exception('Unable to process request due to unable to find DataExtension based on Name');
            }
        }
    }

    /**
     * Patch this instance.
     *
     * @return PatchRequest     Object of type ET_Patch which contains http status code, response, etc from the PATCH SOAP
     *                      service
     */
    public function patch()
    {
        $this->getCustomerKey();
        $originalProps = $this->props;
        $overrideProps = [];
        $fields = [];

        foreach ($this->props as $key => $value) {
            $fields[] = ['Name' => $key, 'Value' => $value];
        }
        $overrideProps['CustomerKey'] = $this->CustomerKey;
        $overrideProps['Properties'] = ['Property' => $fields];

        $this->props = $overrideProps;
        $response = parent::patch();
        $this->props = $originalProps;

        return $response;
    }

    /**
     * Delete this instance.
     *
     * @return DeleteRequest     Object of type ET_Delete which contains http status code, response, etc from the DELETE
     *                       SOAP service
     */
    public function delete()
    {
        $this->getCustomerKey();
        $originalProps = $this->props;
        $overrideProps = [];
        $fields = [];

        foreach ($this->props as $key => $value) {
            $fields[] = ['Name' => $key, 'Value' => $value];
        }
        $overrideProps['CustomerKey'] = $this->CustomerKey;
        $overrideProps['Keys'] = ['Key' => $fields];

        $this->props = $overrideProps;
        $response = parent::delete();
        $this->props = $originalProps;

        return $response;
    }
}
