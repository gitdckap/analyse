<?php

namespace Dckap\MultiAccount\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Index
 * @package Dckap\MultiAccount\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    protected $customerFactory;
    protected $dckapHelper;
    protected $formKey;
    protected $_customerSession;
    public $_storeManager;

    protected $authentication;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \DCKAP\Extension\Helper\Data $dckapHelper,
        CustomerSession $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Model\Authentication $authentication
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->customerFactory = $customerFactory;
        $this->dckapHelper = $dckapHelper;
        $this->formKey = $formKey;
        $this->_customerSession = $customerSession;
        $this->_storeManager=$storeManager;
        $this->authentication = $authentication;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|string
     */
    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();
        $data = array();
        try {
            $params = $this->getRequest()->getParams();
            $userData = '';
            $customer = $this->getCustomer($params['email']);
            if ($customer && $customer->getId()) {
                $authentication = $this->authentication->authenticate($customer->getId(), $params['password']);
                if ($authentication) {
                    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('validate_user');
                    if ($status) {
                        $users = $this->clorasDDIHelper->validateEcommUser($integrationData, $params['email']);
                        if ($users && isset($users['isValid']) && $users['isValid'] == 'yes') {
                            $this->_customerSession->setMultiUserEnable(1);
                            $this->_customerSession->setIsEcommUservalid($users['isValid']);
                            $this->_customerSession->setEcommUserData($users['user']);
                            if (isset($users['user']) && count($users['user']) > 1) {
                                $loginUrl = $this->_storeManager->getStore()->getBaseUrl() . 'customer/account/loginPost';
                                //if (count($user['user']) > 1) {
                                $this->_customerSession->unsMultiUserEnable();
                                $this->_customerSession->setMultiUserEnable(2);
                                foreach ($users['user'] as $key => $user) {
                                    $userData .= "<tr class='child-row'>
                                    <td data-th='Name' class='multiact-name'><p>" . $user['firstName'] . ' ' . $user['lastName'] . "</p></td>
                                     <td data-th='Company' class='multiact-company-name'><p>" . $user['billCompanyName'] . "</p></td>
                                        <td data-th='Email' class='multiact-account-number'><p>" . $user['accountNumber'] . "</p></td>
                                        <td data-th='Email'  class='multiact-user-id'><p>" . $user['userId'] . "</p></td>
                                        <td data-th='Action' class='action'>
                                            <form method='POST' action='" . $loginUrl . "' id='multi-account-" . $key . "' class='sales-person-list'>
                                                <input name='form_key' type='hidden' value='" . $this->formKey->getFormKey() . "' />
                                                <input type='hidden' name='login[username]' value='" . $params['email'] . "' class='sp-name' />
                                                <input type='hidden' name='login[password]' value='" . str_repeat("*", strlen($params['password'])) . "' class='sp-con-mail' />
                                                <div class='etc-val'>
                                                <input type='hidden' name='login[acc_no]' value='" . $user['accountNumber'] . "' class='sp-acc-no' />
                                                <input type='hidden' name='login[acc_detail]' value='" . json_encode($user,JSON_HEX_APOS) . "' class='sp-acc-detail' />
                                                </div>
                                                <input type='submit' value='Login' class='log-button' />
                                            </form>
                                        </td>
                                    </tr>";
                                }
                                // }
                            } elseif (isset($users['user']) && count($users['user']) == 1) {
                                $data['status'] = 'SUCCESS';
                                $data['data'] = "";
                            }
                        } elseif (isset($user['isValid']) && $user['isValid'] == 'no') {
                            
                            $this->_customerSession->setIsEcommUservalid($users['isValid']);
                            $this->_customerSession->setEcommUserErrorMessage($user['errorMessage']);
                        }
                    }
                } else {
                    $data['status'] = 'FAILURE';
                    $data['msg'] = "Invalid Login or Password";
                }
            }

            $data['status'] = 'SUCCESS';
            $data['data'] = $userData;
        } catch (\Exception $e) {
//           return $e->getMessage();
            $data['status'] = 'FAILURE';
            $data['msg'] = $e->getMessage();
        }
        return $resultJson->setData($data);
    }

    public function getCustomer($email)
    {
        $websiteId = $this->dckapHelper->getCurrentWebsiteId();
        $customerModel = $this->customerFactory->create()->setWebsiteId($websiteId);
        return $customerModel->loadByEmail($email);
    }
}
