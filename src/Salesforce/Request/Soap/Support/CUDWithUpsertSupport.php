<?php

namespace Salesforce\Request\Soap\Support;

use Salesforce\Request\Soap\PatchRequest;

/**
 * This class represents the put operation for SOAP service.
 */
class CUDWithUpsertSupport extends CUDSupport
{
    /**
     * @return PatchRequest     Object of type ET_Patch which contains http status code, response, etc from the PATCH SOAP
     *                      service
     */
    public function put()
    {
        $originalProps = $this->props;
        if (property_exists($this, 'folderProperty') && null !== $this->folderProperty && null !== $this->folderId) {
            $this->props[$this->folderProperty] = $this->folderId;
        }
        $response = new PatchRequest($this->authStub, $this->obj, $this->props, true);
        $this->props = $originalProps;

        return $response;
    }
}
