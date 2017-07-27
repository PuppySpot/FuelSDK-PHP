<?php

namespace Salesforce\Request\Rest\Support;

use Exception;
use Salesforce\Request\Rest\BaseObjectRest;
use Salesforce\Request\Rest\GetRequest;

/**
 * This class represents the get operation for REST service.
 */
class GetSupportRest extends BaseObjectRest
{
    /**
     * @var      int   The last page number
     */
    protected $lastPageNumber;

    // method for calling a Fuel API using GET

    /**
     * @return GetRequest Object of type ET_GetRest which contains http status code, response, etc from the GET REST
     *                    service
     * @throws \Exception
     */
    public function get()
    {
        $this->authStub->refreshToken();
        $completeURL = $this->endpoint;
        $additionalQS = [];

        if (null !== $this->props) {
            foreach ($this->props as $key => $value) {
                if (in_array($key, $this->urlProps)) {
                    $completeURL = str_replace("{{$key}}", $value, $completeURL);
                } else {
                    $additionalQS[$key] = $value;
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
        $response = new GetRequest($this->authStub, $completeURL, $queryString);

        if (property_exists($response->results, 'page')) {
            $this->lastPageNumber = $response->results->page;
            $pageSize = $response->results->pageSize;

            $count = null;
            if (property_exists($response->results, 'count')) {
                $count = $response->results->count;
            } elseif (property_exists($response->results, 'totalCount')) {
                $count = $response->results->totalCount;
            }

            if ($count && ($count > ($this->lastPageNumber * $pageSize))) {
                $response->moreResults = true;
            }
        }

        return $response;
    }

    /**
     * @return GetRequest    returns more response from the GET REST service of type ET_GetRest Object
     */
    public function getMoreResults()
    {
        $originalPageValue = 1;
        $removePageFromProps = false;

        if ($this->props && array_key_exists('$page$', $this->props)) {
            $originalPageValue = $this->props['page'];
        } else {
            $removePageFromProps = true;
        }

        if (!$this->props) {
            $this->props = [];
        }

        $this->props['$page'] = $this->lastPageNumber + 1;

        $response = $this->get();

        if ($removePageFromProps) {
            unset($this->props['$page']);
        } else {
            $this->props['$page'] = $originalPageValue;
        }

        return $response;
    }
}
