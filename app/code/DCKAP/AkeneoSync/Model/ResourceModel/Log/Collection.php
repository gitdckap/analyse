<?php

namespace DCKAP\AkeneoSync\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    /**
     * This variable contains a string value
     *
     * @var string $_idFieldName
     */
    protected $_idFieldName = 'log_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\DCKAP\AkeneoSync\Model\Log::class, \DCKAP\AkeneoSync\Model\ResourceModel\Log::class);
    }
}
