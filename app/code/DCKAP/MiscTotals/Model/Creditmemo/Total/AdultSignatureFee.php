<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class AdultSignatureFee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setAdultSignatureFee(0);
        
        $amount = $creditmemo->getOrder()->getAdultSignatureFee();
        $creditmemo->setAdultSignatureFee($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getAdultSignatureFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getAdultSignatureFee());

        return $this;
    }
}
