<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \DCKAP\MiscTotals\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Dckap\MiscTotals\Helper\Data $dataHelper,
        \Magento\Checkout\Model\SessionFactory $_checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->dataHelper = $dataHelper;
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $enabled = $this->dataHelper->isModuleEnabled();
        if ($enabled) {
            if ($addressInformation->getShippingAddress()) {
                $quote = $this->quoteRepository->getActive($cartId);
                $checkoutSession = $this->_checkoutSession->create();

                $miscTotal = $checkoutSession->getMiscTotal();
                if ($miscTotal && $miscTotal > 0) {
                    $quote->setAdultSignatureFee($miscTotal);
                } else {
                        $quote->setAdultSignatureFee(0);
                }
            }
        }
    }
}
