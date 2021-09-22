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

namespace Bss\CheckoutCustomField\Plugin\Model;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class PaymentInformationManagement
{
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Bss\CheckoutCustomField\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PaymentInformationManagement constructor.
     * @param JsonHelper $jsonHelper
     * @param \Bss\CheckoutCustomField\Helper\Data $helper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement$subject
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->helper->moduleEnabled()) {
            try {
                $customAttr = $paymentMethod->getExtensionAttributes()->getBssCustomfield();
                $quote = $this->cartHelper->getQuote();
                $data = [];
                $customField = $quote->getBssCustomfield();
                if ($customField) {
                    $data = $this->jsonHelper->jsonDecode($customField);
                }
                if (is_array($data) && !empty($customAttr)) {
                    foreach ($customAttr as $key => $val) {
                        $data[$key] = $val;
                    }
                }
                $quote->setBssCustomfield($this->jsonHelper->jsonEncode($data));
                $quote->save();
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(_('Something went wrong save custom field'));
            }
        }
    }
}
