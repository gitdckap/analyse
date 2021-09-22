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

namespace Cayan\Payment\Gateway\Helper;

use Magento\Payment\Gateway\Helper\SubjectReader as CoreSubjectReader;

/**
 * Payment Subject Reader
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class SubjectReader
{
    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return CoreSubjectReader::readPayment($subject);
    }

    /**
     * Reads transaction from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readTransaction(array $subject)
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new \InvalidArgumentException(__('Response object does not exist.'));
        }

        return $subject['object']->transaction;
    }
}
