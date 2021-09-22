<?php

namespace DCKAP\AkeneoSync\Model\ResourceModel\ImportStep;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    /**
     * This variable contains a string value
     *
     * @var string $_idFieldName
     */
    protected $_idFieldName = 'step_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\DCKAP\AkeneoSync\Model\ImportStep::class, \DCKAP\AkeneoSync\Model\ResourceModel\ImportStep::class);
    }
}
