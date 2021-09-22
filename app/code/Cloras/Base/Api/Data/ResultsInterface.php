<?php

namespace Cloras\Base\Api\Data;

interface ResultsInterface
{
    const RESPONSE = 'response';

    /**
     * @return \Cloras\Base\Api\Data\ResultsInterface[]|null
     */
    public function getResponse();

    /**
     * @param \Cloras\Base\Api\Data\ResultsInterface $response
     *
     * @return ResultsInterface
     */
    public function setResponse($response);
}//end interface
