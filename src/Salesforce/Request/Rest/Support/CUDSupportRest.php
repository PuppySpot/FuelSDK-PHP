<?php

namespace Salesforce\Request\Rest\Support;

use Exception;
use Salesforce\Request\Rest\DeleteRequest;
use Salesforce\Request\Rest\PatchRequest;
use Salesforce\Request\Rest\PostRequest;

/**
 * This class represents the create, update, delete operation for REST service.
 */
class CUDSupportRest extends GetSupportRest
{
    /**
     * @var      string      Folder property e.g. "Category", "CategoryID", etc.
     */
    protected $folderProperty;

    /**
     * @var      string      Folder Media Type e.g. "dataextension", "triggered_send", etc.
     */
    protected $folderMediaType;

    // method for calling a Fuel API using POST

    /**
     * @return PostRequest Object of type ET_PostRest which contains http status code, response, etc from the POST REST
     *                     service
     * @throws \Exception
     */
    public function post()
    {
        $this->authStub->refreshToken();
        $completeURL = $this->endpoint;
        $additionalQS = [];

        if (null !== $this->props) {
            foreach ($this->props as $key => $value) {
                if (in_array($key, $this->urlProps)) {
                    $completeURL = str_replace("{{$key}}", $value, $completeURL);
                }
            }
        }

        foreach ($this->urlPropsRequired as $value) {
            if (null === $this->props || in_array($value, $this->props)) {
                throw new Exception("Unable to process request due to missing required prop: {$value}");
            }
        }

        // Clean up not required URL parameters
        foreach ($this->urlProps as $value) {
            $completeURL = str_replace("{{$value}}", '', $completeURL);
        }

        $additionalQS['access_token'] = $this->authStub->getAuthToken();
        $queryString = http_build_query($additionalQS);
        $completeURL = "{$completeURL}?{$queryString}";

        return new PostRequest($this->authStub, $completeURL, $this->props);
    }

    // method for calling a Fuel API using PATCH

    /**
     * @return PatchRequest Object of type ET_PatchRest which contains http status code, response, etc from the PATCH
     *                      REST service
     * @throws \Exception
     */
    public function patch()
    {
        $this->authStub->refreshToken();
        $completeURL = $this->endpoint;
        $additionalQS = [];

        // All URL Props are required when doing Patch
        foreach ($this->urlProps as $value) {
            if (null === $this->props || !array_key_exists($value, $this->props)) {
                throw new Exception("Unable to process request due to missing required prop: {$value}");
            }
        }


        if (null !== $this->props) {
            foreach ($this->props as $key => $value) {
                if (in_array($key, $this->urlProps)) {
                    $completeURL = str_replace("{{$key}}", $value, $completeURL);
                }
            }
        }
        $additionalQS['access_token'] = $this->authStub->getAuthToken();
        $queryString = http_build_query($additionalQS);
        $completeURL = "{$completeURL}?{$queryString}";

        return new PatchRequest($this->authStub, $completeURL, $this->props);
    }

    // method for calling a Fuel API using DELETE

    /**
     * @return DeleteRequest Object of type ET_DeleteRest which contains http status code, response, etc from the
     *                       DELETE REST service
     * @throws \Exception
     */
    public function delete()
    {
        $this->authStub->refreshToken();
        $completeURL = $this->endpoint;
        $additionalQS = [];

        // All URL Props are required when doing Delete
        foreach ($this->urlProps as $value) {
            if (null === $this->props || !array_key_exists($value, $this->props)) {
                throw new Exception("Unable to process request due to missing required prop: {$value}");
            }
        }

        if (null !== $this->props) {
            foreach ($this->props as $key => $value) {
                if (in_array($key, $this->urlProps)) {
                    $completeURL = str_replace("{{$key}}", $value, $completeURL);
                }
            }
        }
        $additionalQS['access_token'] = $this->authStub->getAuthToken();
        $queryString = http_build_query($additionalQS);
        $completeURL = "{$completeURL}?{$queryString}";

        return new DeleteRequest($this->authStub, $completeURL);
    }
}
