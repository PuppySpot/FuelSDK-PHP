<?php

namespace Salesforce\Util;

use Salesforce\Request\Rest\Support\CUDSupportRest;

/**
 * Represents an asset associated with a campaign.
 */
class CampaignAsset extends CUDSupportRest
{
    /**
     * Initializes a new instance of the class and will assign endpoint, urlProps, urlPropsRequired fields of parent
     * ET_BaseObjectRest
     */
    public function __construct()
    {
        $this->endpoint = 'https://www.exacttargetapis.com/hub/v1/campaigns/{id}/assets/{assetId}';
        $this->urlProps = ['id', 'assetId'];
        $this->urlPropsRequired = ['id'];
    }
}
