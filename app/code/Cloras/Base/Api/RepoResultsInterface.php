<?php

namespace Cloras\Base\Api;

interface RepoResultsInterface
{

    /**
     * @param string $requestParams
     *
     * @return \Cloras\Base\Api\Data\RepoItemsInterface
     */
    public function getSearchResults();
}//end interface
