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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Gift Card Code Remove Controller
 *
 * @package Cayan\Payment\Controller
 * @author Igor Miura
 */
class Remove extends Action
{
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;

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
     * Remove the gift card code from the cart
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam('giftcode')) {
            $giftCardCode = $this->getRequest()->getParam('giftcode');
            $result = $this->helper->removeGiftCodeFromCart($giftCardCode);

            if ($result) {
                $this->messageManager->addSuccessMessage(__('Gift card successfully removed from order.'));
            } else{
                $this->messageManager->addErrorMessage(__('The requested gift card could not be removed from your order.'));
            }
        } else{
            $this->messageManager->addErrorMessage(__('This gift card code is invalid. Please check it and try again.'));
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
