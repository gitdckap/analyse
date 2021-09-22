<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(
        EventObserver $observer
    ) {
        $quote = $observer->getQuote();
        $adultSignatureFee = $quote->getAdultSignatureFee();
        if (!$adultSignatureFee) {
            return $this;
        }

        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('adult_signature_fee', $adultSignatureFee);

        return $this;
    }
}
