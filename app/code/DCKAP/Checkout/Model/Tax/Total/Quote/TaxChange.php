<?php
namespace Dckap\Checkout\Model\Tax\Total\Quote;

use Magento\Checkout\Model\SessionFactory as CheckoutSession;

class TaxChange
{
    protected $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function afterCollect(\Magento\Tax\Model\Sales\Total\Quote\Tax $subject, $result, $quote, $shippingAssignment, $total)
    {
        $checkoutSession = $this->checkoutSession->create();
        $checkoutData = $checkoutSession->getCheckoutData();
        if ($checkoutData && !empty($checkoutData)) {
            $quote = $checkoutSession->getQuote();
            $ddiTaxAmount = 0.00;
            if (isset($checkoutData[$quote->getId()])) {
                $ddiTaxAmount = $checkoutData[$quote->getId()];
                $total->setGrandTotal($total->getGrandTotal() + $ddiTaxAmount);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() + $ddiTaxAmount);
                $total->setTaxAmount($ddiTaxAmount);
                $total->setBaseTaxAmount($ddiTaxAmount);
            }
        }
        return $result;
    }
}
