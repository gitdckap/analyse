<?php


namespace Dckap\Theme\Block;

use Dckap\Theme\Helper\Data;
use \Magento\Framework\View\Element\Template;

class UsefulLinks extends Template
{
    /**
     * @var Data
     */
    public $themeHelper;

    /**
     * UsefulLinks constructor.
     * @param Template\Context $context
     * @param Data $themeHelper
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $themeHelper,
        array $data = []
    ) {
        $this->themeHelper = $themeHelper;

        parent::__construct($context, $data);
    }

    /**
     * Return useful links name and href for the footer
     *
     * @return array|bool|float|int|mixed|string|null
     */
    public function getFooterUsefulLinks()
    {
        $linksData = $this->themeHelper->getUseFulLinksData();
        return array_slice($this->themeHelper->unserialize($linksData), 0, 6);
    }
}
