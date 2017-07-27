<?php

namespace Salesforce\Request\Soap;

use Salesforce\Client;
use Salesforce\Util\Util;
use Salesforce\Request\Request;
use SoapVar;
use stdClass;

/**
 * This class represents the GET operation for SOAP service.
 */
class GetRequest extends Request
{
    /**
     * Initializes a new instance of the class.
     *
     * @param    Client $authStub             The ET client object which performs the auth token, refresh token using
     *                                        clientID clientSecret
     * @param    string $objType              Object name, e.g. "ImportDefinition", "DataExtension", etc
     * @param    array  $props                Dictionary type array which may hold e.g. array('id' => '', 'key' => '')
     * @param    array  $filter               Dictionary type array which may hold e.g. array("Property"=>"",
     *                                        "SimpleOperator"=>"","Value"=>"")
     * @param    bool      $getSinceLastBatch Gets or sets a boolean value indicating whether to get since last batch.
     *                                        true if get since last batch; otherwise, false.
     */
    public function __construct($authStub, $objType, $props, $filter, $getSinceLastBatch = false)
    {
        $authStub->refreshToken();
        $rrm = [];
        $request = [];
        $retrieveRequest = [];

        // If Props is not sent then Info will be used to find all retrievable properties
        if (null === $props) {
            $props = [];
            $info = new InfoRequest($authStub, $objType);
            if (is_array($info->results)) {
                foreach ($info->results as $property) {
                    if ($property->IsRetrievable) {
                        $props[] = $property->Name;
                    }
                }
            }
        }

        if (Util::isAssoc($props)) {
            $retrieveProps = [];
            foreach ($props as $key => $value) {
                if (!is_array($value)) {
                    $retrieveProps[] = $key;
                }
                $retrieveRequest['Properties'] = $retrieveProps;
            }
        } else {
            $retrieveRequest['Properties'] = $props;
        }

        $retrieveRequest['ObjectType'] = $objType;
        if ('Account' === $objType) {
            $retrieveRequest['QueryAllAccounts'] = true;
        }
        if ($filter) {
            if (array_key_exists('LogicalOperator', $filter)) {
                $cfp = new stdClass();
                $cfp->LeftOperand = new SoapVar($filter['LeftOperand'], \SOAP_ENC_OBJECT, 'SimpleFilterPart', 'http://exacttarget.com/wsdl/partnerAPI');
                $cfp->RightOperand = new SoapVar($filter['RightOperand'], \SOAP_ENC_OBJECT, 'SimpleFilterPart', 'http://exacttarget.com/wsdl/partnerAPI');
                $cfp->LogicalOperator = $filter['LogicalOperator'];
                $retrieveRequest['Filter'] = new SoapVar($cfp, \SOAP_ENC_OBJECT, 'ComplexFilterPart', 'http://exacttarget.com/wsdl/partnerAPI');
            } else {
                $retrieveRequest['Filter'] = new SoapVar($filter, \SOAP_ENC_OBJECT, 'SimpleFilterPart', 'http://exacttarget.com/wsdl/partnerAPI');
            }
        }
        if ($getSinceLastBatch) {
            $retrieveRequest['RetrieveAllSinceLastBatch'] = true;
        }


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
            if ($return->OverallStatus !== 'OK' && $return->OverallStatus !== 'MoreDataAvailable') {
                $this->status = false;
                $this->message = $return->OverallStatus;
            }

            $this->moreResults = false;

            if ($return->OverallStatus === 'MoreDataAvailable') {
                $this->moreResults = true;
            }

            $this->request_id = $return->RequestID;
        }
    }
}
