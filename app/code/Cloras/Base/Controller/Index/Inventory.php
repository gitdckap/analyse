<?php
namespace Cloras\Base\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Inventory extends Action
{
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Category $categoryModel
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->priceHelper        = $priceHelper;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->dir = $dir;
        $this->customerSession = $customerSession;
        $this->categoryModel = $categoryModel;
        parent::__construct($context);
    }//end __construct()

    public function execute()
    {

        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->dir->getRoot().'/var/log/cloras/dynamic-inventory.log'));
        $this->logger->info('ajax call started');
        
    
        $categoryId = $this->getRequest()->getParam('categoryId');

        $productId = $this->getRequest()->getParam('productId');

        $limit = ($this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 24);

        $pageValue = ($this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1);

        $products = [];
        $sessionPrices = [];
        $items = [];

        list($status, $customerData, $integrationData, $filterBy, $isLoggedIn) = $this->helper->isServiceEnabled(
            'dynamic_inventory'
        );
    
        
        $productsData = [];
    
        $this->logger->info('status '.$status);

       
    
    
        $productIds = explode(",", $this->getRequest()->getParam('productIds'));
          
        
    

        $items = $this->helper->getProductItems(
            $productIds,
            $sessionPrices,
            $qty = 1,
            $filterBy
        );
        
        $this->logger->info('INTEGRATION DATA', $integrationData);
        //Enabled only dynamic inventory
               
        if ($status) {
            if (!empty($items)) {
                $location_id = 0;
        
                if ($isLoggedIn) {
                    if (array_key_exists('location_id', $integrationData)) {
                        $location_id = $integrationData['location_id'];
                    }
                }
                $products = $this->getMatchedProducts($items, $location_id);
            }
        }
        
    
        
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
    
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                $products
            )
        );
        $this->logger->info('ajax call end');
        return $response;
    }//end execute()


    public function getMatchedProducts($productItems, $location_id)
    {

        $products = [];
        foreach ($productItems as $key => $value) {
            $productId = $value['product_id'];
            $qty = $value['qty'];
           
            if (array_key_exists('inv_mast_uid', $value) && array_key_exists('uom', $value)) {
            
                $qty = $this->helper->fetchDynamicInventory($value['inv_mast_uid'], $value['uom'], $location_id);
            }


            $products[] = [
                'productId' => $productId,
                'qty'  => $qty
            ];
        }

        return $products;
    }
}//end class
