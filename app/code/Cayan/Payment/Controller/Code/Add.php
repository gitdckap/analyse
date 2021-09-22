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
use Magento\Framework\Controller\ResultFactory;
use Cayan\Payment\Model\Helper\Discount as DiscountHelper;

/**
 * Gift Card Code Add Action
 *
 * @package Cayan\Payment\Controller
 * @author Igor Miura
 */
class Add extends Action
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     */
    public function __construct(Context $context, DiscountHelper $discountHelper)
    {
        parent::__construct($context);

        $this->helper = $discountHelper;
    }

    /**
     * Check if possible to add the request coupon code to the cart
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        $giftCardCode = $this->getRequest()->getParam('giftcode');
        $pin = $this->getRequest()->getParam('pin');

        if (empty($pin)) {
            $pin = null;
        } else {
            $pin = (int)$pin;
        }

        if ($this->getRequest()->getParam('isAjax')) {
            $output = ['status' => 0, 'applied_discount' => 0];

            if (!empty($giftCardCode)) {
                $output['status'] = $this->helper->addCodeToCart($giftCardCode, $pin);
                $output['applied_discount'] = $this->helper->getAmountDiscountInQuote();
            } else {
                $output['status'] = -1;
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($output));

            return null;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!empty($giftCardCode)) {
            $result = $this->helper->addCodeToCart($giftCardCode, $pin);

            if ($result === DiscountHelper::MESSAGE_CODE_ADDED) {
                $this->messageManager->addSuccessMessage(__('Gift card balance successfully applied to order total.'));
            } elseif ($result === DiscountHelper::MESSAGE_CODE_ALREADY_ADD) {
                $this->messageManager->addErrorMessage(__('This gift card has already been applied to your order.'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('Invalid gift card. Please check the entered code and try again.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Invalid gift card. Please check the entered code and try again.')
            );
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
