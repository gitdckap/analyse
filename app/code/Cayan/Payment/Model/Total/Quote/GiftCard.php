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

namespace Cayan\Payment\Model\Total\Quote;

use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Gift Card Quote Total Model
 *
 * @package Cayan\Payment\Model\Total\Quote
 */
class GiftCard extends AbstractTotal
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $discountHelper;

    /**
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     */
    public function __construct(DiscountHelper $discountHelper)
    {
        $this->discountHelper = $discountHelper;
    }

    /**
     * Collect quote totals
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $discountAmount = $this->discountHelper->getGiftcardAmountApplied($quote);

        $total->addTotalAmount('cayancard', -$discountAmount);
        $total->addBaseTotalAmount('cayancard', -$discountAmount);
        $total->setGrandTotal((($total->getGrandTotal() - $discountAmount) < 0) ? 0
            : ($total->getGrandTotal() - $discountAmount));
        $total->setBaseGrandTotal((($total->getBaseGrandTotal() - $discountAmount) < 0) ? 0
            : ($total->getBaseGrandTotal() - $discountAmount));
        $quote->setCayanGiftcardAmount($discountAmount*-1);

        return $this;
    }
}
