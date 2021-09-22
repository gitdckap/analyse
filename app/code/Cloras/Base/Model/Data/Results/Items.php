<?php

namespace Cloras\Base\Model\Data\Results;

use Cloras\Base\Api\Data\ResultsInterface;

class Items implements ResultsInterface
{
    private $response;

    public function __construct()
    {
        $this->response = [];
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\ResultsInterface[]|null
     */
    public function getResponse()
    {
        return $this->response;
    }//end getResponse()

    /**
     * @param \Cloras\Base\Api\Data\ResultsInterface $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this->response;
    }//end setResponse()
}//end class
