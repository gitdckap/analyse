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

namespace Cayan\Payment\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;

/**
 * Gift Card Credit Memo Total Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class GiftCard extends AbstractTotal
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param array $data
     */
    public function __construct(CartRepositoryInterface $cartRepository, array $data = [])
    {
        parent::__construct($data);

        $this->cartRepository = $cartRepository;
    }

    /**
     * Collect Credit Memo totals
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $quoteId = $order->getQuoteId();
        $quote = $this->cartRepository->get($quoteId);
        $giftTotal = 0;

        if ($quote->getCayanGiftcardAmount() != null) {
            $giftTotal = $quote->getCayanGiftcardAmount() * -1;
        }

        // Discount gift card only on full order refund.
        if ($giftTotal <= 0) {
            return;
        }

        if ($creditmemo->getBaseSubtotalInclTax() === $order->getBaseSubtotalInclTax()) {
            $this->applyDiscount($creditmemo, $giftTotal);
        } elseif ($creditmemo->getBaseGrandTotal() > $order->getBaseTotalPaid()) {
            $giftTotal = $order->getBaseTotalPaid();

            $this->applyDiscount($creditmemo, $giftTotal, true);
        }
    }

    /**
     * Apply gift card to order total as discount
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param float $giftTotal
     * @param bool $partial
     */
    private function applyDiscount(Creditmemo $creditmemo, $giftTotal, $partial = false)
    {
        if (!$partial) {
            $totalBaseGrandTotal = $creditmemo->getBaseGrandTotal();

            if ($giftTotal > $totalBaseGrandTotal) {
                $totalBaseGrandTotal = 0;
            } else {
                $totalBaseGrandTotal = $totalBaseGrandTotal - $giftTotal;
            }

            $totalGrandTotal = $creditmemo->getGrandTotal();

            if ($giftTotal > $totalGrandTotal) {
                $totalGrandTotal = 0;
            } else {
                $totalGrandTotal = $totalGrandTotal - $giftTotal;
            }
        } else {
            $totalBaseGrandTotal = $giftTotal;
            $totalGrandTotal = $giftTotal;
        }

        $creditmemo->setBaseGrandTotal($totalBaseGrandTotal);
        $creditmemo->setGrandTotal($totalGrandTotal);

        if ($totalGrandTotal <= 0) {
            $creditmemo->setAllowZeroGrandTotal(true);
        }
    }
}
