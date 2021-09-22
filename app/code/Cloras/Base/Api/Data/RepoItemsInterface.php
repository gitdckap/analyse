<?php

namespace Cloras\Base\Api\Data;

interface RepoItemsInterface
{
    const RESULTS = 'results';

    /**
     * @return \Cloras\Base\Api\Data\RepoItemsInterface[]|null
     */
    public function getResults();

    /**
     * @param \Cloras\Base\Api\Data\RepoItemsInterface $results
     *
     * @return ItemsInterface
     */
    public function setResults($results);
}//end interface
