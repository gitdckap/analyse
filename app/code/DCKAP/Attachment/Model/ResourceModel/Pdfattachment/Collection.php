<?php

namespace Dckap\Attachment\Model\ResourceModel\Pdfattachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\Attachment\Model\Pdfattachment', 'Dckap\Attachment\Model\ResourceModel\Pdfattachment');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
}
