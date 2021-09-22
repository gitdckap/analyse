<?php
namespace Dckap\StorePickup\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * category   Dckap
 * package    Dckap_StorePickup
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    const EXTENSION_URL = 'http://www.dckap.com/';

    /**
     * @var \Dckap\StorePickup\Helper\Data $helper
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Dckap\StorePickup\Helper\Data          $helper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Dckap\StorePickup\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $extensionVersion   = $this->_helper->getExtensionVersion();
        $versionLabel       = sprintf(
            '<a href="%s" title="Store Pickup" target="_blank">%s</a>',
            self::EXTENSION_URL,
            $extensionVersion
        );
        $element->setValue($versionLabel);

        return $element->getValue();
    }
}
