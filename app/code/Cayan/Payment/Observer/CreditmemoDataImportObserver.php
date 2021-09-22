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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Credit Memo Data Import Observer
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
class CreditmemoDataImportObserver implements ObserverInterface
{
    /**
     * Refund the order back to the applied Gift Card
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $input = $observer->getEvent()->getInput();

        if (isset($input['refund_cayancard_return'])) {
            $inputValue = $input['refund_cayancard_return'];

            if (is_numeric($inputValue)) {
                $creditmemo->setData('cayancard_return_request', (float)$inputValue);
            }
        }

        return $this;
    }
}
