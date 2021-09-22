<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2017 Classy Llama
 */

namespace DCKAP\DefaultBillingAddress\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    const COMPONENT_PATH = 'DCKAP_DefaultBillingAddress/js/view/billing-address';

    const NO_EDIT_PAYMENT_CHILD = 'before-place-order';

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutDataHelper;

    /**
     * LayoutProcessor constructor.
     * @param \Magento\Checkout\Helper\Data $checkoutDataHelper
     */
    public function __construct(
        \Magento\Checkout\Helper\Data $checkoutDataHelper
    ) {
        $this->checkoutDataHelper = $checkoutDataHelper;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $paymentLayout = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children'];

        if ($this->checkoutDataHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            foreach ($paymentLayout['payments-list']['children'] as $key => $method) {
                if ($key != self::NO_EDIT_PAYMENT_CHILD) {
                    $paymentLayout['payments-list']['children'][$key]['component'] = self::COMPONENT_PATH;
                }
            }
        } else {
            $paymentLayout['afterMethods']['children']['billing-address-form']['component'] = self::COMPONENT_PATH;
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
            ['children'] = $paymentLayout;

        return $jsLayout;
    }
}
