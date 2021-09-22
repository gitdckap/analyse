<?php

namespace DCKAP\Catalog\Plugin;

class Collection
{
    protected $dckapHelper;

    public function __construct(
        \DCKAP\Extension\Helper\Data $dckapHelper
    ) {
        $this->dckapHelper = $dckapHelper;
    }

    public function aroundLoadEntities(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, callable $proceed, $printQuery = false, $logQuery = false)
    {
        $pageSize = $subject->getPageSize();
        $searchEngine = $this->dckapHelper->getSearchEngine();
        if ($searchEngine == '' || $searchEngine == 'mysql') {
            $subject->setPageSize($pageSize);
        } else {
            $subject->setPageSize(0);
        }
        $result = $proceed($printQuery, $logQuery);
        return $result;
    }
}
