<?php
namespace Dckap\Attachment\Model\ResourceModel;

class Pdfattachment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dckap_product_pdf_attachment', 'id');
    }
}
