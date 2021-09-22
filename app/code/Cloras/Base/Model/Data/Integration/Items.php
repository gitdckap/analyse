<?php

namespace Cloras\Base\Model\Data\Integration;

use Cloras\Base\Api\Data\IntegrationItemsInterface;

class Items implements IntegrationItemsInterface
{
    private $response;

    public function __construct()
    {
        $this->response = [];
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface[]|null
     */
    public function getResponseMessage()
    {
        return $this->response;
    }//end getResponseMessage()

    /**
     * @param \Cloras\Base\Api\Data\IntegrationItemsInterface $response
     *
     * @return $this
     */
    public function setResponseMessage($response)
    {
        $this->response[] = $response;

        return $this->response;
    }//end setResponseMessage()
}//end class
