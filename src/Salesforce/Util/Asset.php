<?php

namespace Salesforce\Util;

use Salesforce\Request\Rest\Support\CUDSupportRest;
use stdClass;

/**
 *    An asset is an instance of any kind of content in the CMS.
 */
class Asset extends CUDSupportRest
{
    /**
     * The constructor will assign endpoint, urlProps, urlPropsRequired fields of parent ET_BaseObjectRest
     */
    public function __construct()
    {
        $this->endpoint = 'https://www.exacttargetapis.com/guide/v1/contentItems/portfolio/{id}';
        $this->urlProps = ['id'];
        $this->urlPropsRequired = [];
    }

    // method for calling a Fuel API using POST

    /**
     * @return \stdClass     The stdClass object with property httpcode and body from the REST service after upload is
     *                    finished
     */
    public function upload()
    {
        $completeURL = 'https://www.exacttargetapis.com/guide/v1/contentItems/portfolio/fileupload?access_token=' . $this->authStub->getAuthToken();

        $post = ['file_contents' => '@' . $this->props['filePath']];

        $ch = curl_init();

        $headers = ['User-Agent: ' . Util::getSDKVersion()];
        curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, \CURLOPT_URL, $completeURL);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, \CURLOPT_POST, 1);
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $post);

        // Disable VerifyPeer for SSL
        curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);

        $outputJSON = curl_exec($ch);
        //curl_close ($ch);

        $responseObject = new stdClass();
        $responseObject->body = $outputJSON;
        $responseObject->httpcode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $responseObject;
    }

    /**
     * @return null
     */
    public function patch()
    {
        return null;
    }

    /**
     * @return null
     */
    public function delete()
    {
        return null;
    }
}