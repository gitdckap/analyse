<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class AdultSignatureFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setAdultSignatureFee(0);
        
        $amount = $invoice->getOrder()->getAdultSignatureFee();
        $invoice->setAdultSignatureFee($amount);
       

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getAdultSignatureFee());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getAdultSignatureFee());

        return $this;
    }
}
