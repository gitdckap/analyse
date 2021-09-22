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

namespace Cayan\Payment\Gateway\Config\Credit;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Cayan\Payment\Gateway\Helper\SubjectReader;

/**
 * Can Void Credit Transaction Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class CanVoidHandler implements ValueHandlerInterface
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
     * Check whether this action can be handled
     *
     * @param array $subject
     * @param int|null $storeId
     * @return bool
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);

        return is_null($paymentDataObject->getPayment()->getData('base_amount_paid_online'));
    }
}
