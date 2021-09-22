<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2018 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Block\Sales\Order\GiftCard;

use Cayan\Payment\Block\Sales\Order\GiftCard;
use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteRepository;

/**
 * Gift Card Order Refunded Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Refunded extends GiftCard
{
    /**
     * Check if gift card usage was refunded
     *
     * @return bool
     */
    public function isCardRefunded()
    {
        $refundedAmount = $this->getRefundedAmount();

        return $refundedAmount > 0;
    }

    /**
     * Retrieve refunded amount from order
     *
     * @return float
     */
    public function getRefundedAmount()
    {
        $usages = $this->codeHistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id_fk', $this->getOrder()->getId())
            ->addFieldToFilter('balance_used', ['lt' => 0]);
        $total = 0;

        foreach ($usages as $usage) {
            $total = $total + $usage->getBalanceUsed();
        }

        return $total * -1;
    }

    /**
     * Retrieve all gift card code labels
     *
     * @return string
     */
    public function getCodesLabel()
    {
        $label = '';
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $this->codeHistoryFactory->create()->getCollection()
            ->addFieldToFilter('order_id_fk', $this->getOrder()->getId())
            ->addFieldToFilter('balance_used', ['lt' => 0])
            ->addFieldToSelect('code_id_fk');
        $codeIds = [];

        foreach ($codeHistoryCollection as $codeHistory) {
            $codeIds[] = $codeHistory->getCodeIdFk();
        }

        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $this->codeFactory->create()->getCollection()
            ->addFieldToFilter('code_id', ['in' => $codeIds])
            ->addFieldToSelect('code');

        foreach ($codeCollection as $code) {
            if ($label === '') {
                $label = $this->generalHelper->maskGiftCard($code->getCode());
            } else {
                $label .= ',' . $this->generalHelper->maskGiftCard($code->getCode());
            }
        }

        return $label;
    }

    /**
     * Initialize customer balance order total
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->isCardRefunded()) {
            $total = new DataObject(
                [
                    'code' => 'cayancard_refunded',
                    'label' => __('Gift Card Refunded (%1)', $this->getCodesLabel()),
                    'value' => $this->getRefundedAmount(),
                    'base_value' => $this->getRefundedAmount()
                ]
            );
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }
}
