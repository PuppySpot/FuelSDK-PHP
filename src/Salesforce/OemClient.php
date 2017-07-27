<?php

use Salesforce\Client;
use Salesforce\Request\Rest\GetRequest;
use Salesforce\Request\Rest\PutRequest;

/**
 * The class can create and retrieve specific tenant.
 */
class OemClient extends Client
{
    /**
     * @param array $tenantInfo Dictionary type array which may hold e.g. array('key' => '')
     *
     * @return PutRequest
     */
    public function CreateTenant($tenantInfo)
    {
        $key = $tenantInfo['key'];
        unset($tenantInfo['key']);
        $additionalQS = [];
        $additionalQS['access_token'] = $this->getAuthToken();
        $queryString = http_build_query($additionalQS);
        $completeURL = "https://www.exacttargetapis.com/provisioning/v1/tenants/{$key}?{$queryString}";

        return new PutRequest($this, $completeURL, $tenantInfo);
    }

    /**
     * @return GetRequest     Object of type ET_GetRest which contains http status code, response, etc from the GET
     *                        REST service
     */
    public function GetTenants()
    {
        $additionalQS = [];
        $additionalQS['access_token'] = $this->getAuthToken();
        $queryString = http_build_query($additionalQS);
        $completeURL = "https://www.exacttargetapis.com/provisioning/v1/tenants/?{$queryString}";

        return new GetRequest($this, $completeURL, $queryString);
    }
}
