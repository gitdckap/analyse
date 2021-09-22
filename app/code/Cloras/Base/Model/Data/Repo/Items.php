<?php

namespace Cloras\Base\Model\Data\Repo;

use Cloras\Base\Api\Data\RepoItemsInterface;

class Items implements RepoItemsInterface
{
    private $results;

    public function __construct()
    {
        $this->results = [];
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\RepoItemsInterface[]|null
     */
    public function getResults()
    {
        return $this->results;
    }//end getResults()

    /**
     * @param \Cloras\Base\Api\Data\RepoItemsInterface $results
     *
     * @return $this
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }//end setResults()
}//end class
