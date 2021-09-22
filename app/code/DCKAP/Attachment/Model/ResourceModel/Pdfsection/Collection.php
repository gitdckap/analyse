<?php

namespace Dckap\Attachment\Model\ResourceModel\Pdfsection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\Attachment\Model\Pdfsection', 'Dckap\Attachment\Model\ResourceModel\Pdfsection');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
