<?php

namespace Cloras\Base\Api;

interface IntegrationInterface
{

    /**
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface
     */
    public function testCredentials();

    /**
     * @param string $batchInfo
     *
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface
     */
    public function createIntegration($batchInfo);

    /**
     * @param string $name
     * @param string $email
     * @return \Cloras\Base\Api\Data\IntegrationItemsInterface
     */
    public function createNewIntegration($name, $email);
}//end interface
