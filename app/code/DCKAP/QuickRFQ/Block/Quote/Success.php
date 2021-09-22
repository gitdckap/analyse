<?php
namespace Dckap\QuickRFQ\Block\Quote;

class Success extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Price Quote Successfully Submitted'));
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
