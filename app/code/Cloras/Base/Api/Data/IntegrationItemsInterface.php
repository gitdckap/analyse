<?php

namespace Cloras\Base\Api\Data;

interface IntegrationItemsInterface
{
    const RESPONSE = 'response';

    /**
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface[]|null
     */
    public function getResponseMessage();

    /**
     * @param \Cloras\Base\Api\Data\IntegrationItemsInterface $message
     *
     * @return IntegrationItemsInterface
     */
    public function setResponseMessage($message);
}//end interface
