<?php

namespace Dckap\MultiAccount\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Index
 * @package Dckap\MultiAccount\Controller\Index
 */
class Company extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;


    /**
     * Company constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CustomerSession $customerSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        $data['company'] = '';
        try {
            $customData = $this->customerSession->getCustomData();
            if ($this->customerSession->isLoggedIn()) {
                $cutomerName = $this->customerSession->getCustomer();
                $FullName = $cutomerName->getName();
            } else {
                $FullName = "";
            }
            $multiUserEnable = $this->customerSession->getMultiUserEnable();

            if ($multiUserEnable == 2) {
                if (isset($customData['billCompanyName']) && $customData['billCompanyName'] != '') {
                    $data['status'] = 'SUCCESS';
                    $data['company'] = '<div class="multi-user-company"><div class="left">Welcome, ' . $FullName . '! </div><div class="right">Shopping in: <span>' . $customData["billCompanyName"] . '</span></div></div>';
                    $data['company_b2b_theme'] = '<div class="multi-user-company">Shopping in: <span>' . $customData["billCompanyName"] . '</span></div>';
                    $data['multi_user'] = 2;
                }
            }
        } catch (\Exception $e) {
            $data['status'] = 'FAILURE';
            $data['msg'] = $e->getMessage();
        }
        return $resultJson->setData($data);
    }
}
