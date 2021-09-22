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

use Cayan\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Transaction Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransactionHandler implements HandlerInterface
{
    /**
     * @var \Cayan\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handle the response
     *
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($handlingSubject);
        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $paymentDataObject->getPayment();

        if (!($orderPayment instanceof Payment)) {
            return;
        }

        $this->setTransactionId($orderPayment, $response);

        $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());

        $closed = $this->shouldCloseParentTransaction($orderPayment);

        $orderPayment->setShouldCloseParentTransaction($closed);
    }

    /**
     * Set the transaction ID
     *
     * @param Payment $orderPayment
     * @param array $response
     */
    private function setTransactionId(Payment $orderPayment, array $response)
    {
        $orderPayment->setTransactionId($response['AuthorizationCode']);
    }

    /**
     * Determine whether the transaction should be closed
     *
     * @return bool
     */
    private function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * Determine whether the parent transaction should be closed
     *
     * @param \Magento\Sales\Model\Order\Payment $orderPayment
     * @return bool
     */
    private function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}
