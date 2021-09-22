<?php

namespace Dckap\MultiAccount\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
   
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $sessionFactory;

    protected $session;
    protected $json;
    protected $regionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->session = $customerSession;
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        $this->json = $json;
        $this->regionFactory = $regionFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Return store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return WebsiteId
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    public function IsCustomerLogged(){
         $customerSession = $this->sessionFactory->create();
         return $customerSession->isLoggedIn();
    }

    public function getErpEcommUserData()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->getEcommData()) {
            $customerData = $customerSession->getEcommData()[0];
            return $customerData;
        }
        return false;
    }

    public function getValidateUserData()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->getCustomData()) {
            $customerData = $customerSession->getCustomData();
            return $customerData;
        }
        return false;
    }

    public function getMultiAccountData($field="") {
        if($this->session->getCustomData()) {
            if ($field) {
                $splittedData = $this->session->getCustomData();
                if (isset($splittedData[$field]) && $splittedData[$field]) {
                    return $splittedData[$field];
                }
            } else {
                return $this->session->getCustomData();
            }
        }
        return false;
    }

    public function getStateName($code,$countryId = 'US'){
        return $this->regionFactory->create()->loadByCode($code,$countryId)->getName();
    }

    public function IsMultiAccount(){
        return $this->session->getMultiUserEnable();
    }

    public function IsCartisEmpty(){
        return $this->checkoutSession->getQuote()->getItemsCollection();
    }
}
