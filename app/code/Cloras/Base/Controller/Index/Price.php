<?php
namespace Cloras\Base\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;

class Price extends Action
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
        Session $customerSession,
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

    
        $this->logger->info('ajax call started');
            
        $categoryId = $this->getRequest()->getParam('categoryId');

        $productId = $this->getRequest()->getParam('productId');

        $limit = ($this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 24);

        $pageValue = ($this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1);

        $products = [];
        $price = [];
        $items = [];

        list($status, $customerData, $integrationData, $filterBy, $isLoggedIn) = $this->helper->isServiceEnabled(
            \Cloras\Base\Helper\Data::SERVICE_TYPE
        );
        
        $productsData = [];

        $this->logger->info('status '.$status);

        $selectedColumns = [
            'sku',
            'entity_id',
            'qty',
            'price',
            'stock_status'
        ];

        if ($filterBy != 'sku') {
            array_push($selectedColumns, $filterBy);
        }
    
        $productIds = explode(",", $this->getRequest()->getParam('productIds'));
          
        $sessionPrices = [];
        if (!empty($this->customerSession->getClorasCustomPrice())) {
            //$sessionPrices = $this->customerSession->getClorasCustomPrice();
        }

        $this->logger->info('ajax product session prices', (array) $this->customerSession->getClorasCustomPrice());
        $items = $this->helper->getProductItems(
            $productIds,
            $sessionPrices,
            $qty = 1,
            $filterBy
        );
        
        //Enabled only dynamic prices
               
        if ($status) {
       
            if (!empty($items)) {
                $price = $this->helper->fetchDynamicPrice(
                    $customerData,
                    $integrationData,
                    $items,
                    $itemId = 0,
                    $qty = 1,
                    $isLoggedIn
                );
                $this->logger->info('API prices ', $price);
            }
        }
        
        if (!empty($sessionPrices)) {
            $products = $this->getMatchedProducts($sessionPrices, $price);
        }
    
       
        $products = $this->getMatchedProducts($items, $price);

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

    public function getMatchedProducts($productItems, $price)
    {
    
        $products = [];
        foreach ($productItems as $key => $value) {
            $productId = $value['product_id'];
            $qty = $value['qty'];
            $api_price = $value['price'];

            if (array_key_exists($value['item_id'], $price)) {
                if (array_key_exists('price', $price[$value['item_id']])) {
                    if ($price[$value['item_id']]['price'] != 0) {
                        $api_price = $price[$value['item_id']]['price'];
                    }
                }
            }

            $products[] = [
                'productId' => $productId,
                'price'     => $this->priceHelper->currency($api_price, true, false),
                'qty'  => $qty
            ];
        }

        return $products;
    }
}//end class
