<?php
namespace Dckap\ProductImport\Block\Adminhtml;

class Attributeimport extends  \Magento\Backend\Block\Template
{
    protected $_template = 'attributeimport.phtml';

    public function __construct(
            \Magento\Backend\Block\Template\Context $context
    ) { 
        parent::__construct($context);
    }

    public function getSampleCsvUrl()
    {
        return $this->getUrl('productimport/attributeimport/samplecsv/');
    }
}