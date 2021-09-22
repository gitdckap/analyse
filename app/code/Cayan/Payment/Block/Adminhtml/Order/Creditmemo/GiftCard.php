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

namespace Cayan\Payment\Block\Adminhtml\Order\Creditmemo;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteRepository;

/**
 * Gift Card Credit Memo Admin Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class GiftCard extends Template
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QuoteRepository $quoteRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->quoteRepository = $quoteRepository;
        $this->order = $registry->registry('current_creditmemo')->getOrder();
    }

    /**
     * Get current gift card amount
     *
     * @return float
     */
    public function getGiftCardAmount()
    {
        $quote = $this->quoteRepository->get($this->order->getQuoteId());

        return $quote->getCayanGiftcardAmount() * -1;
    }
}
