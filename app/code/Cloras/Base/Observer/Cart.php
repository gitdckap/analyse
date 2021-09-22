<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

class Cart implements ObserverInterface
{
    private $registry = null;

    private $logger;

    private $helper;

    private $customerFactory;

    private $customerResourceFactory;

    private $customer;

    private $customerData;

    private $objectManager;

    private $session;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $session,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Data\Customer $customerModelData,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->registry                = $registry;
        $this->logger                  = $logger;
        $this->helper                  = $helper;
        $this->customerFactory         = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->customer                = $customer;
        $this->customerModelData            = $customerModelData;
        $this->productRepository       = $productRepository;
        $this->session                 = $session;
    }//end __construct()

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteitem = $observer->getEvent()->getData('quote_item');

        $product  = $observer->getEvent()->getData('product');
        $price    = [];
        $cusprice = 0;

        $this->helper->makeDir();

        static $items    = [];

        list($status, $customerData, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
            'dynamic_pricing'
        );
        if ($status) {
            $itemId       = '';
            $qty          = $quoteitem->getQty();
            $cusprice     = $product->getPrice();
            $productIds = [];
            $sessionPrices = [];

            if ($product->getTypeId() == 'simple') {
                $itemId = $product->getSku();
            } else {
                $itemId = $quoteitem->getSku();
            }
            
            $productsRepo = $this->productRepository->get($itemId);
            if ($filterBy != 'sku') {
                if (is_object($productsRepo->getCustomAttribute($filterBy))) {
                    $itemId = $productsRepo->getCustomAttribute($filterBy)->getValue();
                }
            }
            $productIds[] = $productsRepo->getId();
                
            $items = $this->helper->getProductItems(
                $productIds,
                $sessionPrices,
                $qty,
                $filterBy
            );
            
            if (!empty($items)) {
                $price = $this->helper->fetchDynamicPrice(
                    $customerData,
                    $integrationData,
                    $items,
                    $itemId,
                    $qty
                );

                if (!empty($price)) {
                    if (array_key_exists($itemId, $price)) {
                        if (array_key_exists('price', $price[$itemId])) {
                                $cusprice = $price[$itemId]['price'];
                        }
                    }
                }
            }

            if (round($cusprice) != 0) {
                $quoteitem->setCustomPrice($cusprice);
                $quoteitem->setOriginalCustomPrice($cusprice);

                $quoteitem->getProduct()->setIsSuperMode(true);

                return $this;
            }
        }//end if
    }//end execute()
}//end class
