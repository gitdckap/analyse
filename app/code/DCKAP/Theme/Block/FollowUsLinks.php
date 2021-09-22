<?php
namespace Dckap\Theme\Block;

use \Magento\Framework\View\Element\Template;
use Dckap\Theme\Helper\Data;

class FollowUsLinks extends Template
{
    public function __construct(
        Template\Context $context,
        Data $themeHelper,
        array $data = []
    ) {
        $this->themeHelper=$themeHelper;
        parent::__construct($context, $data);
    }

    public function getFollowUsLinks()
    {
        $followUsLinksData= $this->themeHelper->getFollowUsLinks();
        return $this->themeHelper->unserialize($followUsLinksData);
    }
}
