<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_CustomerApproval
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CustomerApproval\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\CustomerApproval\Helper\Data;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeAction;

/**
 * Class Approve
 *
 * @package Mageplaza\CustomerApproval\Controller\Adminhtml\Index
 */
class Approve extends Action
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Approve constructor.
     *
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helperData = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/after-account-creation.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Inside Approve Function");
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index');

        $customerId = (int) $this->getRequest()->getParam('id', 0);
        if (!$customerId) {
            return $resultRedirect;
        }

        $customer = $this->helperData->getCustomerById($customerId);
        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return $resultRedirect;
        }

        $approveStatus = $this->getRequest()->getParam('status');
        try {
            if ($approveStatus == AttributeOptions::APPROVED) {
                $actionResult = $this->helperData->approvalCustomerById($customerId, TypeAction::EDITCUSTOMER);
                $logger->info("After approving by Customer Id");
                $logger->info($actionResult);
             //   $logger->info($actionResult['errorMessage']);
                if (isset($actionResult['isValid']) && $actionResult['isValid'] == 'no') {
                    if (($actionResult['errorMessage']) != null) {
                        $logger->info("Error message +++");
                        $this->messageManager->addWarningMessage($actionResult['errorMessage']);
                    }
                }
                $this->messageManager->addSuccessMessage(__('Customer account has been approved!'));
            } else {
                $actionResult = $this->helperData->notApprovalCustomerById($customerId);
                $this->messageManager->addSuccessMessage(__('Customer account has not been approved!'));
            }
        } catch (Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
        }

        $resultRedirect->setPath('customer/*/edit', ['id' => $customerId]);

        return $resultRedirect;
    }
}
