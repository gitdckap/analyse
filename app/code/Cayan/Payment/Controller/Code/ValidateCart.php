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

namespace Cayan\Payment\Controller\Code;

use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Validate Cart Controller
 *
 * Checks whether current quote can be finished using gift card.
 *
 * @package Cayan\Payment\Controller
 * @author Igor Miura
 */
class ValidateCart extends Action
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Cayan\Payment\Model\Helper\Discount $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        DiscountHelper $helper,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Validate the cart
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $availableAmount = $this->helper->getAmountDiscountInQuote(null, true);
        $requestedAmount = $this->checkoutSession->getQuote()->getCayanGiftcardAmount();
        $output = [];

        if (($availableAmount === 0 && is_null($requestedAmount)) || $availableAmount === ($requestedAmount * -1)) {
            $output['result'] = 1;
        } else {
            $this->checkoutSession->getQuote()->collectTotals()->save();
            $output['result'] = 0;
        }
        $output['available'] = $availableAmount;

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($output));
    }
}
