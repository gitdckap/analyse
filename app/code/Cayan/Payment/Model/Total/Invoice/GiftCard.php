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

namespace Cayan\Payment\Model\Total\Invoice;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Gift Card Invoice Total Model
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
     * Collect invoice totals
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $quoteId = $order->getQuoteId();
        $quote = $this->cartRepository->get($quoteId);

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $quote->getCayanGiftcardAmount());
        $invoice->setGrandTotal($invoice->getGrandTotal() + $quote->getCayanGiftcardAmount());

        return $this;
    }
}
