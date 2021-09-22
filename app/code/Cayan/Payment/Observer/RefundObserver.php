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

namespace Cayan\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Message\ManagerInterface;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Model\Helper\Discount as CayanHelper;

/**
 * Refund Observer
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
class RefundObserver implements ObserverInterface
{
    /**
     * @var \Cayan\Payment\Model\CodeHistoryFactory
     */
    private $codeHistoryFactory;
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Cayan\Payment\Model\Helper\Discount $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        CodeHistoryFactory $codeHistoryFactory,
        CayanHelper $helper,
        ManagerInterface $messageManager
    ) {
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * Refund applied gift card(s)
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditmemo->getOrder();
        $giftcardAmount = $creditmemo->getData('cayancard_return_request');

        if (!$giftcardAmount) {
            return $this;
        }

        $transactionError = [];
        $error = false;
        $refundedAmount = 0;
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $this->codeHistoryFactory->create()->getCollection()
            ->addFieldToFilter('order_id_fk', $order->getId());

        foreach ($codeHistoryCollection as $codeHistory) {
            if ($error || $giftcardAmount <= 0) {
                break;
            }

            $result = $this->helper->refund($codeHistory, $giftcardAmount);

            if ($result > 0) {
                $giftcardAmount = $giftcardAmount - $result;
                $refundedAmount = $refundedAmount + $result;
            } else {
                $error = true;
                $transactionError[] = $codeHistory->getTransactionCode();
            }
        }

        if ($error) {
            foreach ($transactionError as $error) {
                $this->messageManager->addErrorMessage(
                    __('A gift card could not be refunded. Transaction error: "%1"', $error)
                );
            }
        }

        if ($refundedAmount > 0) {
            $this->messageManager->addSuccessMessage(__('Gift card refunded.'));
        }

        return $this;
    }
}
