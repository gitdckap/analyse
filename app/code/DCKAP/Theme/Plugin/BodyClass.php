<?php

namespace Dckap\Theme\Plugin;

use Magento\Framework\App\ResponseInterface;
use Dckap\Theme\Helper\Data;

class BodyClass
{

    private $_context;
    protected $_themeHelper;

    /**
     * BodyClass constructor.
     * @param \Magento\Framework\View\Element\Context $_context
     * @param Data $themeHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $_context,
        Data $themeHelper
    ) {
        $this->context = $_context;
        $this->_themeHelper=$themeHelper;
    }

    /**
     * Adding class to body tag depending on website mode
     *
     * @param \Magento\Framework\View\Result\Page $subject
     * @param ResponseInterface $response
     * @return ResponseInterface[]
     */
    public function beforeRenderResult(
        \Magento\Framework\View\Result\Page $subject,
        ResponseInterface $response
    ) {
        $websitMode=$this->_themeHelper->getWebsiteMode();
        $subject->getConfig()->addBodyClass($websitMode);
        return [$response];
    }
}
