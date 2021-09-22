<?php

namespace Dckap\CsvConverter\Block\Adminhtml\Attachment;

class Index extends \Magento\Backend\Block\Widget\Container
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
