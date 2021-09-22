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

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Cayan\Payment\Gateway\Helper\SubjectReader;

/**
 * Void Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class VoidHandler implements HandlerInterface
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

        if (!($paymentDataObject->getPayment() instanceof Payment)) {
            return;
        }

        /** @var Payment $orderPayment */
        $orderPayment = $paymentDataObject->getPayment();

        $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());

        $closed = $this->shouldCloseParentTransaction($orderPayment);

        $orderPayment->setShouldCloseParentTransaction($closed);
    }

    /**
     * Determine whether parent transaction should be closed
     *
     * @param \Magento\Sales\Model\Order\Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }

    /**
     * Determine whether transaction should be closed
     *
     * @return bool
     */
    private function shouldCloseTransaction()
    {
        return true;
    }
}
