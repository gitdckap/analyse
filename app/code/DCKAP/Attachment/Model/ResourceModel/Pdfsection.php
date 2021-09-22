<?php
namespace Dckap\Attachment\Model\ResourceModel;

class Pdfsection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('dckap_product_pdf_attachment_section', 'id');
    }
}
