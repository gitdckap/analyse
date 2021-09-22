<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Block\Checkout\Cart;

use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Cayan\Payment\Gateway\Config\Gift\Config as GiftConfig;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Block\Cart as CartBlock;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;

/**
 * Gift Card Code Cart Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Code extends CartBlock
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    protected $helper;
    /**
     * @var \Cayan\Payment\Gateway\Config\Gift\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     * @param \Cayan\Payment\Gateway\Config\Gift\Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        Url $catalogUrlBuilder,
        Cart $cartHelper,
        HttpContext $httpContext,
        DiscountHelper $discountHelper,
        GiftConfig $config,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );

        $this->helper = $discountHelper;
        $this->config = $config;
    }

    /**
     * Retrieve the form URL
     *
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('cayan/code/add', ['_secure' => true]);
    }

    /**
     * Retrieve the URL used to call the checkBalance controller via AJAX
     *
     * @return string
     */
    public function getCheckBalanceUrl()
    {
        return $this->getUrl('cayan/code/checkBalance', ['_secure' => true]);
    }

    /**
     * Retrieve the maximum gift card code length from configuration
     *
     * @return int
     */
    public function getCodeLength()
    {
        return $this->helper->getCodeMaxLength();
    }

    /**
     * Check if gift card is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * Retrieve the method title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->helper->getTitle();
    }

    /**
     * Retrieve the loading image URL
     *
     * @return string
     */
    public function getLoadingImageUrl()
    {
        return $this->_assetRepo->getUrl('Cayan_Payment::images/rolling.gif');
    }

    /**
     * Check if PIN field is to be displayed
     *
     * @return bool
     */
    public function showPin()
    {
        return $this->config->isPinEnabled($this->_storeManager->getStore()->getId());
    }
}
