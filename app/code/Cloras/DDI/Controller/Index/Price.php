<?php
namespace Cloras\DDI\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Price extends Action
{
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\DDI\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jsonHelper        = $jsonHelper;
        $this->_productModel      = $productModel;
        $this->priceHelper        = $priceHelper;
        $this->registry = $registry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockItem = $stockItem;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->listProduct = $listProduct;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }//end __construct()

    public function execute()
    {
		$this->logger->info('ajax call started');
        $productId = $this->getRequest()->getParam('productId');

        $categoryId = $this->getRequest()->getParam('categoryId');

        $limit = ($this->getRequest()->getParam('limit') ? $this->getRequest()->getParam('limit') : 30);

        $pageValue = $this->getRequest()->getParam('page');
        if (empty($pageValue)) {
            $pageValue = 1;
        }

        $pricePrecision = 2;

        $products = [];
        $price = [];
        $items = []; // Iyappan added.
        list($status, $p21CustomerId, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
            'dynamic_pricing_and_inventory'
        );
	
        $sessionPrices = [];
        $productsData = [];       
		$this->logger->info('get Ajax price controller started');
        if ($categoryId) {
            $this->logger->info('Current Category ID'.$categoryId);
            //Iyappan added to get subcategory of parent category. During parent category display.
            /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $catId = $categoryId; // Parent Category ID
            $subcategory = $objectManager->create('Magento\Catalog\Model\Category')->load($catId);*/

            $category = $this->categoryFactory->create()->load($categoryId); //vishnu modified
            $subcats = $category->getChildrenCategories();
            foreach ($subcats as $subcat) {
                $categories[] = $subcat->getId();
            }
            // Iyappan end.
            $categories[] = $categoryId;//category ids array // Iyappan changed it.
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(array('sku','entity_id','qty','price'));
            $collection->setPageSize($limit);
            $collection->setCurPage($pageValue);
            $collection->addCategoriesFilter(['in' => $categories]);
			
			
           
            /*echo $collection->getSelect()->__toString();
            exit;*/
             $productIds = [];

            /*$collection = $this->listProduct->getLoadedProductCollection();
            $collection->addCategoriesFilter(['in' => $categories]);*/
            foreach ($collection as $product) {
                $productIds[] = $product->getId();
                $itemId = $product->getSku();
                $api_price = $product->getPrice();
                $qty = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                $productsData[$itemId] = [ 'product_id' => $product->getId(), 'sku' => $itemId, 'price' => $api_price, 'qty' => $qty ];
            }            
           
           
        } else {
            $product   = $this->_productModel->load($productId);
            
            $itemId = $product->getSku();
            
            $api_price = $product->getPrice();
            
            $productIds[] = $product->getId();

            $qty = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());

            $productsData[$itemId] = [ 'product_id' => $product->getId(), 'sku' => $itemId, 'price' => $api_price, 'qty' => $qty ];
          
        }
    
        $this->logger->info('ajax product session prices', (array) $this->customerSession->getClorasCustomPrice());
        //if Dynamic pricing services enabled
        if ($status) {
            //echo "price 2";
                
            if (!empty($this->customerSession->getClorasCustomPrice())) {
                $sessionPrices = $this->customerSession->getClorasCustomPrice();
            }
            #print_r($sessionPrices); //vishnu comment
            $items = $this->helper->getProductItems(
                $productIds,
                $sessionPrices,
                $qty = 1,
                $filterBy
            );
            //echo "item start";
            //$items = [];
           // print_r($items);exit;
            //echo "item end";
            if (count($items)) { // Iyappan changed.
                //echo "fetchDynamicPrice";
                $price = $this->helper->fetchDynamicPrice(
                    $p21CustomerId,
                    $integrationData,
                    $items,
                    $itemId = 0,
                    $qty = 1
                );
                $this->logger->info('API prices ', $price);
            }
        }     

        foreach ($productsData as $key => $value) {
            $productId = $value['product_id'];
            $qty = $value['qty'];
            $api_price = $value['price'];
         

            if (array_key_exists($value['sku'], $price)) {
                if ($price[$value['sku']]['price'] > 0) {
                    $api_price = number_format($price[$value['sku']]['price'], $pricePrecision, ".", "");
                }
                $qty = $price[$value['sku']]['qty'];                    
            }
            if (array_key_exists($value['sku'], $sessionPrices)) {
                if ($sessionPrices[$value['sku']]['price'] > 0) {
                    $api_price = number_format($sessionPrices[$value['sku']]['price'], $pricePrecision, ".", "");
                }
                $qty = $sessionPrices[$value['sku']]['qty'];                    
            }
            $products[] = [
                'productId' => $productId,
                'price'     => $this->priceHelper->currency($api_price, true, false),
                'qty'  => $qty
            ];
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
}//end class
