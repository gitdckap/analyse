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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Cayan\Payment\Model\Helper\Discount as CayanHelper;
use Cayan\Payment\Helper\Data as CayanGeneralHelper;
use Cayan\Payment\Gateway\Config\Credit\Config;
use Psr\Log\LoggerInterface;

/**
 * Gift Card Order Cancel Observer
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
class CancelOrder implements ObserverInterface
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $generalHelper;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $config;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Cayan\Payment\Model\Helper\Discount $helper
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $config
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CayanHelper $helper,
        CayanGeneralHelper $generalHelper,
        Config $config,
        ManagerInterface $messageManager,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Refund applied gift cards when an order is cancelled
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());

        if ($this->config->debug()) {
            $this->logger->debug(__('Cancel Order Id: %1', $order->getId()));
            $this->logger->debug(__('Cayan Gift Card Amount: %1', $quote->getCayanGiftcardAmount()));
        }

        if (!is_null($quote->getCayanGiftcardAmount())) {
            $result = $this->helper->refundGiftCardInOrder($order->getId(), true);

            if ($result) {
                $this->messageManager->addSuccessMessage(__('The gift cards applied to the order have been refunded.'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('A problem occurred while refunding the gift cards applied to the order.')
                );
            }
        }
    }
}
