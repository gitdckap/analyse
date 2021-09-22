<?php
    /**
     * @author     DCKAP
     * @package    DCKAP_MiscTotals
     * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
     * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */

namespace DCKAP\MiscTotals\Model\Quote\Total;

class AdultSignatureFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $dataHelper;

    protected $priceCurrency;
    protected $clorasDDIHelper;

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;
    protected $QuoteFactory;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \DCKAP\MiscTotals\Helper\Data $dataHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Quote\Model\QuoteFactory $QuoteFactory,
        \Magento\Checkout\Model\SessionFactory $_checkoutSession
    ) {
        $this->quoteValidator = $quoteValidator;
        $this->priceCurrency = $priceCurrency;
        $this->dataHelper = $dataHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->QuoteFactory = $QuoteFactory;
        $this->_checkoutSession = $_checkoutSession;
    }

		/**
		 * @param \Magento\Quote\Model\Quote $quote
		 * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
		 * @param \Magento\Quote\Model\Quote\Address\Total $total
		 * @return $this|\Magento\Quote\Model\Quote\Address\Total\AbstractTotal
		 */
		public function collect(
				\Magento\Quote\Model\Quote $quote,
				\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
				\Magento\Quote\Model\Quote\Address\Total $total
		) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/miscamt.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
			parent::collect($quote, $shippingAssignment, $total);
			if (!count($shippingAssignment->getItems())) {
				return $this;
			}
            $checkoutSession = $this->_checkoutSession->create();
			$miscTotal = $adultSignatureFee = $this->dataHelper->getAdultSignatureFee($quote->getId());
			if($miscTotal==0){
				$miscTotal = $checkoutSession->getMiscTotal();
			}
            $logger->info("miscTotal - ". print_r($miscTotal, true));
            if ($miscTotal && $miscTotal > 0 ) {
                $enabled = $this->dataHelper->isModuleEnabled();
                $logger->info('config status - '.print_r($enabled, true));
                if ($enabled) {
                    if ($quote->getShippingAddress()) {
//                        $adultSignatureFee = $this->dataHelper->getAdultSignatureFee($quote->getId());
                    $adultSignatureFee = $miscTotal;
                    if ($adultSignatureFee != 0) {
                        $total->setTotalAmount('adult_signature_fee', $adultSignatureFee);
                        $total->setBaseTotalAmount('adult_signature_fee', $adultSignatureFee);
                        $total->setAdultSignatureFee($adultSignatureFee);
                        $quote->setAdultSignatureFee($adultSignatureFee);
                    } else {
                        $total->setTotalAmount('adult_signature_fee', 0);
                        $total->setBaseTotalAmount('adult_signature_fee', 0);
                        $total->setAdultSignatureFee(0);
                        $quote->setAdultSignatureFee(0);
                    }
                }
            }
        } else {
            $total->setTotalAmount('adult_signature_fee', 0);
            $total->setBaseTotalAmount('adult_signature_fee', 0);
            $total->setAdultSignatureFee(0);
            $quote->setAdultSignatureFee(0);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $enabled = $this->dataHelper->isModuleEnabled();
        $adultSignatureFee = $quote->getAdultSignatureFee();

        $result = [];
        if ($enabled && $adultSignatureFee) {
            $result = [
                    'code' => 'adult_signature_fee',
                    'title' => $this->getLabel(),
                    'value' => $adultSignatureFee
            ];
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return $this->dataHelper->getAdultSignatureFeeLabel();
    }


    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
//    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
//    {
//        $total->setTotalAmount('subtotal', 0);
//        $total->setBaseTotalAmount('subtotal', 0);
//        $total->setTotalAmount('tax', 0);
//        $total->setBaseTotalAmount('tax', 0);
//        $total->setTotalAmount('discount_tax_compensation', 0);
//        $total->setBaseTotalAmount('discount_tax_compensation', 0);
//        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
//        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
//        $total->setSubtotalInclTax(0);
//        $total->setBaseSubtotalInclTax(0);
//    }
}
