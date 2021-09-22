<?php
namespace Cloras\DDI\Observer;

use Magento\Framework\Event\ObserverInterface;

class createCustomer implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;

    /**
     * @var \DCKAP\Extension\Helper\Data
     */
    protected $extensionHelper;

    /**
     * createCustomer constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \DCKAP\Extension\Helper\Data $extensionHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Cloras\Base\Helper\Data $clorasHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->clorasHelper = $clorasHelper;
        $this->extensionHelper = $extensionHelper;
    }

     public function isB2B()
    {
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if($configValue=="b2b")
            return true;
        else
            return false;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getData('customer');
        try {
            /* create customer api call */
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('create_user');
            if ($status) {
                $responseData = $this->clorasDDIHelper->createCustomer($integrationData, $customer);
            }

            /* Ship To Address save */            
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('ship_to_insert');
            if ($status) {
                $email = $customer->getEmail();
                $items = $this->clorasDDIHelper->shipToInsert($integrationData, $email);
                return $items;
            }
        } catch (\Exception $e)  {
            if ($this->extensionHelper->getIsLogger() && $this->isB2B()) {
                $this->logger->debug($e->getMessage());
            }
        }
        return true;
    }
}
