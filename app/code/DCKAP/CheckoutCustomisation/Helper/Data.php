<?php

namespace DCKAP\CheckoutCustomisation\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\AddressRepositoryInterface;
use Exception;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;
    private $session;
    private $customerSession;
    protected $scopeConfig;

    protected $shipconfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->_customerSession = $customerSession;
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getCustomerAddresses()
    {
        $addressesList = [];
        try {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'parent_id',
                $customerId
            )->create();
            $addressRepository = $this->addressRepository->getList($searchCriteria);
            foreach ($addressRepository->getItems() as $address) {
                $addressesList[] = $address;
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $addressesList;
    }

    public function getManualShipTo()
    {
        $customData = [];
        $customData = $this->_customerSession->getCustomData();
        if (!empty($customData)) {
            return $customData;
        }

        return $customData;
    }

    public function getShippingMethods()
    {

        $pickup_option = null;
        $activeCarriers = $this->shipconfig->getActiveCarriers();
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
                    if ($code == 'ddistorepickup_ddistorepickup') {
                        $pickup_option = true;
                    } else {
                        $pickup_option = false;
                    }
                }
            }
        }
        return $pickup_option;
    }
}
