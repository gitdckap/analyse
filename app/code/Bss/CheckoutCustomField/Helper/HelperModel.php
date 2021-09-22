<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutCustomField\Helper;

use Magento\Store\Model\StoreManagerInterface as StoreId;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class HelperModel extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $attribute;

    /**
     * @var \Bss\CheckoutCustomField\Model\AttributeOption
     */
    protected $attributeOption;

    /**
     * @var \Magento\Backend\Model\Session\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * HelperModel constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $helper
     * @param \Bss\CheckoutCustomField\Model\Attribute $attribute
     * @param \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption
     * @param \Magento\Backend\Model\Session\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        \Bss\CheckoutCustomField\Model\Attribute $attribute,
        \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption,
        \Magento\Backend\Model\Session\QuoteFactory $quoteFactory
    ) {
        $this->helper = $helper;
        $this->attribute = $attribute;
        $this->attributeOption = $attributeOption;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\Session\QuoteFactory
     */
    public function returnQuoteFactory()
    {
        return $this->quoteFactory;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return \Bss\CheckoutCustomField\Model\Attribute
     */
    public function getModelAttribute()
    {
        return $this->attribute;
    }

    /**
     * @return \Bss\CheckoutCustomField\Model\AttributeOption
     */
    public function getModelAttributeOption()
    {
        return $this->attributeOption;
    }
}
