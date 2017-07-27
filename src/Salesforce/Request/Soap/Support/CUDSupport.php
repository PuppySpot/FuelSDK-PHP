<?php

namespace Salesforce\Request\Soap\Support;

use Exception;
use Salesforce\Request\Soap\DeleteRequest;
use Salesforce\Request\Soap\PatchRequest;
use Salesforce\Request\Soap\PostRequest;
use Salesforce\Type\Folder;

/**
 * This class represents the create, update, delete operation for SOAP service.
 */
class CUDSupport extends GetSupport
{

    /**
     * @return PostRequest Object of type ET_Post which contains http status code, response, etc from the POST SOAP service
     * @throws \Exception
     */
    public function post()
    {
        $originalProps = $this->props;
        if (property_exists($this, 'folderProperty') && null !== $this->folderProperty && null !== $this->folderId) {
            $this->props[$this->folderProperty] = $this->folderId;
        } elseif (property_exists($this, 'folderProperty') && null !== $this->authStub->packageName) {
            if (null === $this->authStub->packageFolders) {
                $getPackageFolder = new Folder();
                $getPackageFolder->authStub = $this->authStub;
                $getPackageFolder->props = ['ID', 'ContentType'];
                $getPackageFolder->filter = [
                    'Property' => 'Name', 'SimpleOperator' => 'equals', 'Value' => $this->authStub->packageName,
                ];
                $resultPackageFolder = $getPackageFolder->get();
                if ($resultPackageFolder->status) {
                    $this->authStub->packageFolders = [];
                    foreach ($resultPackageFolder->results as $result) {
                        $this->authStub->packageFolders[$result->ContentType] = $result->ID;
                    }
                } else {
                    throw new Exception('Unable to retrieve folders from account due to: ' . $resultPackageFolder->message);
                }
            }

            if (!array_key_exists($this->folderMediaType, $this->authStub->packageFolders)) {
                if (null === $this->authStub->parentFolders) {
                    $parentFolders = new Folder();
                    $parentFolders->authStub = $this->authStub;
                    $parentFolders->props = ['ID', 'ContentType'];
                    $parentFolders->filter = [
                        'Property' => 'ParentFolder.ID', 'SimpleOperator' => 'equals', 'Value' => '0',
                    ];
                    $resultParentFolders = $parentFolders->get();
                    if ($resultParentFolders->status) {
                        $this->authStub->parentFolders = [];
                        foreach ($resultParentFolders->results as $result) {
                            $this->authStub->parentFolders[$result->ContentType] = $result->ID;
                        }
                    } else {
                        throw new Exception('Unable to retrieve folders from account due to: ' . $resultParentFolders->message);
                    }
                }
                $newFolder = new Folder();
                $newFolder->authStub = $this->authStub;
                $newFolder->props = [
                    'Name'         => $this->authStub->packageName,
                    'Description'  => $this->authStub->packageName,
                    'ContentType'  => $this->folderMediaType,
                    'IsEditable'   => 'true',
                    'ParentFolder' => [
                        'ID' => $this->authStub->parentFolders[$this->folderMediaType],
                    ],
                ];
                $folderResult = $newFolder->post();
                if ($folderResult->status) {
                    $this->authStub->packageFolders[$this->folderMediaType] = $folderResult->results[0]->NewID;
                } else {
                    throw new Exception('Unable to create folder for Post due to: ' . $folderResult->message);
                }
            }
            $this->props[$this->folderProperty] = $this->authStub->packageFolders[$this->folderMediaType];
        }

        $response = new PostRequest($this->authStub, $this->obj, $this->props);
        $this->props = $originalProps;

        return $response;
    }

    /**
     * @return PatchRequest     Object of type ET_Patch which contains http status code, response, etc from the PATCH SOAP
     *                      service
     */
    public function patch()
    {
        $originalProps = $this->props;
        if (property_exists($this, 'folderProperty') && null !== $this->folderProperty && null !== $this->folderId) {
            $this->props[$this->folderProperty] = $this->folderId;
        }
        $response = new PatchRequest($this->authStub, $this->obj, $this->props);
        $this->props = $originalProps;

        return $response;
    }

    /**
     * @return DeleteRequest     Object of type ET_Delete which contains http status code, response, etc from the DELETE
     *                       SOAP service
     */
    public function delete()
    {
        return new DeleteRequest($this->authStub, $this->obj, $this->props);
    }
}
