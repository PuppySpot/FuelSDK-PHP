<?php

namespace Salesforce\DataExtension;

use Salesforce\Request\Soap\PatchRequest;
use Salesforce\Request\Soap\PostRequest;
use Salesforce\Request\Soap\Support\CUDSupport;
use Salesforce\Util\Util;

/**
 * ETDataExtension - Represents a data extension within an account.
 */
class DataExtension extends CUDSupport
{
    /**
     * @var Column[]      Gets or sets array of DE columns.
     */
    public $columns;

    /**
     * Initializes a new instance of the class.
     */
    public function __construct()
    {
        $this->obj = 'DataExtension';
    }

    /**
     * Post this instance.
     *
     * @return PostRequest     Object of type ET_Post which contains http status code, response, etc from the POST SOAP
     *                     service
     */
    public function post()
    {
        $originalProps = $this->props;
        if (Util::isAssoc($this->props)) {
            $this->props['Fields'] = ['Field' => []];
            if (null !== $this->columns && is_array($this->columns)) {
                foreach ($this->columns as $column) {
                    $this->props['Fields']['Field'][] = $column;
                }
            }
        } else {
            $newProps = [];
            foreach ($this->props as $DE) {
                $newDE = $DE;
                $newDE['Fields'] = ['Field' => []];
                if (null !== $DE['columns'] && is_array($DE['columns'])) {
                    foreach ($DE['columns'] as $column) {
                        $newDE['Fields']['Field'][] = $column;
                    }
                }
                $newProps[] = $newDE;
            }
            $this->props = $newProps;
        }

        $response = parent::post();

        $this->props = $originalProps;

        return $response;
    }

    /**
     * Patch this instance.
     *
     * @return PatchRequest     Object of type ET_Patch which contains http status code, response, etc from the PATCH SOAP
     *                      service
     */
    public function patch()
    {
        $this->props['Fields'] = ['Field' => []];
        foreach ($this->columns as $column) {
            $this->props['Fields']['Field'][] = $column;
        }
        $response = parent::patch();
        unset($this->props['Fields']);

        return $response;
    }
}
