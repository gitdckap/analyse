<?php

namespace Dckap\ProductImport\Block\Adminhtml;

class Videoimport extends \Magento\Backend\Block\Template
{
    protected $_template = 'videoimport.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context
    ) {
        parent::__construct($context);
    }

    public function getSampleCsvUrl()
    {
        return $this->getUrl('productimport/videoimport/samplecsv/');
    }

}