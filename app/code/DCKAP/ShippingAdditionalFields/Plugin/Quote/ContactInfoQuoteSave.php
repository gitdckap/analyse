<?php

namespace Dckap\ShippingAdditionalFields\Plugin\Quote;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class ContactInfoQuoteSave
 *
 * @package Dckap\ShippingAdditionalFields\Plugin\Quote
 */
class ContactInfoQuoteSave
{
    /**
     * @var QuoteRepository
     */
    public $quoteRepository;

    /**
     * @var JsonHelper
     */
    public $jsonHelper;

    /**
     * ContactInfoQuoteSave constructor.
     *
     * @param QuoteRepository $quoteRepository
     * @param JsonHelper      $jsonHelper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        JsonHelper $jsonHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement   $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if (!$extAttributes = $addressInformation->getExtensionAttributes()) {
            return;
        }
        $extAttributes = $addressInformation->getExtensionAttributes();
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setDdiDeliveryContactEmail($extAttributes->getDdiDeliveryContactEmail());
        $quote->setDdiDeliveryContactNo($extAttributes->getDdiDeliveryContactNo());
        $quote->setDdiPrefWarehouse($extAttributes->getDdiPrefWarehouse());
        $quote->setDdiPickupDate($extAttributes->getDdiPickupDate());
        
    }
}
