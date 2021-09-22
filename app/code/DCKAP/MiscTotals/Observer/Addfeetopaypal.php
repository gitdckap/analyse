<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Observer;

use Dckap\MiscTotals\Helper\Data;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Addfeetopaypal implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkout;

    /**
     * @var DCKAP\MiscTotals\Helper\Data
     */
    protected $helper;

    /**
     * Addfeetopaypal constructor.
     * @param SessionFactory $checkout
     * @param Data $helper
     */
    public function __construct(
        SessionFactory $checkout,
        Data $helper
    ) {
        $this->checkout = $checkout;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(
        Observer $observer
    ) {
        if (!$this->helper->isModuleEnabled()) {
            return $this;
        }

        $quote = $this->checkout->create()->getQuote();
        $adultSignatureFee = $quote->getAdultSignatureFee();
        if ($adultSignatureFee) {
            $label = $this->helper->getAdultSignatureFeeLabel();
            $cart = $observer->getEvent()->getCart();
            $cart->addCustomItem($label, 1, $adultSignatureFee, $label);
        }
    }
}
