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

namespace Cayan\Payment\Gateway\Credit\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Refund Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class RefundHandler extends VoidHandler
{
    /**
     * Determine whether parent transaction should be closed
     *
     * @param \Magento\Sales\Model\Order\Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return !(bool)$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
