<?php

namespace Dckap\Attachment\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{
    protected $helperData;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    public function getSubmitUrl()
    {
        return $this->helperData->getUrl('attachment/index/save/');
    }

    public function getSampleCsvUrl()
    {
        return $this->helperData->getUrl('attachment/index/samplecsv/');
    }
}
