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

namespace Cayan\Payment\Observer\GiftCard;

use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Gift Card After Order Observer
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
class AfterOrder implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $discountHelper;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        DiscountHelper $discountHelper
    ) {
        $this->discountHelper = $discountHelper;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Subtract the amount of the applied gift card from the order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();

        try {
            $quote = $this->cartRepository->get($quoteId);
            $giftcardAmount = $quote->getCayanGiftcardAmount() * -1;
            $codes = $this->discountHelper->getAddedCodes($quote);

            if (count($codes) > 0) {
                foreach ($codes as $code) {
                    if ($giftcardAmount > 0) {
                        $codeId = $this->discountHelper->getCodeIdByCode($code);
                        $result = $this->discountHelper->addCodeUsage($codeId, $order->getId(), $giftcardAmount);
                        $giftcardAmount -= $result;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return $this;
    }
}
