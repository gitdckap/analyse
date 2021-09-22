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

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Cayan\Payment\Model\Helper\Discount as DiscountHelper;

/**
 * Check Gift Card Balance Controller
 *
 * @package Cayan\Payment\Controller
 * @author Igor Miura
 */
class CheckBalance extends Action
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(Context $context, DiscountHelper $discountHelper, PriceHelper $priceHelper)
    {
        parent::__construct($context);

        $this->helper = $discountHelper;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Retrieve the available balance of requested gift card
     */
    public function execute()
    {
        $giftCardCode = $this->_request->getParam('giftcode');

        if (!empty($giftCardCode)) {
            $availableAmount = $this->helper->getAvailableAmount($giftCardCode, true);

            if ($availableAmount > 0) {
                $output = ['error' => 0, 'message' => $this->priceHelper->currency($availableAmount, true, false)];
            } else {
                $output = ['error' => 2, 'message' => __('Invalid Code.')];
            }
        } else {
            $output = ['error' => 1, 'message' => __('Invalid Code.')];
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($output));
    }
}
