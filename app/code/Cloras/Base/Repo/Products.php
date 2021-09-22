<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\Data\ProductInterfaceFactory;
use Cloras\Base\Api\ProductResultsInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Cloras\Base\Api\Data\ResultsInterfaceFactory;

class Products implements ProductResultsInterface
{
    private $productInterfaceFactory;

    private $searchCriteriaBuilder;

    private $productRepository;

    private $stockRegistry;

    private $catalogProductFactory;

    private $productsFactory;

    private $storeManager;

    private $productCollectionFactory;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        StockRegistryInterface $stockRegistry,
        Json $jsonHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        ResultsInterfaceFactory $resultsFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        \Magento\CatalogInventory\Model\ResourceModel\Stock $productStock
    ) {
        $this->productFactory           = $productFactory;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->productRepository        = $productRepository;
        $this->stockRegistry            = $stockRegistry;
        $this->jsonHelper               = $jsonHelper;
        $this->catalogProductFactory    = $catalogProductFactory;
        $this->storeManager             = $store;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAction = $productAction;
        $this->productStock = $productStock;

        $this->logger = $logger;
        $this->dir = $dir;
        $this->resultsFactory = $resultsFactory;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function getProducts()
    {
        $products    = $this->productFactory->create();
        $productData = [];
        try {
            $productFilters = $this->searchCriteriaBuilder->addFilter('status', '1', 'eq')->create();
            $productItems   = $this->productRepository->getList($productFilters)->getItems();
            $productData    = [];
            $partnumber     = '';
            $uom            = '';
            $total_count = count($productItems);
            if ($total_count) {
                foreach ($productItems as $product) {
                    if (is_object($product->getCustomAttribute('partnumber'))) {
                        $partnumber = $product->getCustomAttribute('partnumber')->getValue();
                    }

                    if (is_object($product->getCustomAttribute('uom'))) {
                        $uom = $product->getAttributeText('uom');
                        if (empty($uom)) {
                            $uom = $product->getCustomAttribute('uom')->getValue();
                        }
                    }

                    $productData[] = [
                        'sku'        => $product->getSku(),
                        'partnumber' => $partnumber,
                        'qty'        => 1,
                        'price_uom'  => $uom,
                        'uom'        => $uom,
                    ];
                }
            }
        } catch (\Exception $e) {
            $productData[] = [
                'status' => 'failure',
                'Error'  => $e->getMessage(),
            ];
        }//end try
        $products->setTotalProducts($total_count);
        $products->addProduct($productData);

        return $products;
    }//end getProducts()

    /**
     * @return \Cloras\Base\Api\ProductResultsInterface
     */
    public function updateProductsInventory($productInfo)
    {
        $response = [];
        try {
            $productInfo = $this->jsonHelper->unserialize($productInfo);
            $storeId     = $this->storeManager->getStore()->getId();
            if (array_key_exists('sku', $productInfo)) {
                $sku = $productInfo['sku'];
                if ($sku) {
                    $productId = $this->getProductId($sku);
                    if ($productId) {
                        $productStockInfo = $this->getStockDetails($productInfo);
                        if ($this->updateProductData($productId, $data = [], $storeId, $productStockInfo)) {
                            $response[] = [
                            'status'  => 'success',
                            'message' => $sku . ' updated',
                            ];
                        }
                    } else {
                        $response[] = [
                            'status'  => 'failure',
                            'message' => $sku . ' is not available',
                        ];
                    }//end if
                }//end if
            } else {
                $response[] = [
                    'status'  => 'failure',
                    'message' => 'SKU Key is not present',
                ];
            }//end if
        } catch (\Exception $e) {
            $response[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }//end try

        $products->setResponseMessage($response);

        return $products;
    }//end updateProductsInventory()

    /**
     * @return \Cloras\Base\Api\Data\ProductResultsInterface
     */
    public function createProducts($data)
    {
        $products     = $this->productFactory->create();
        $messages     = [];
        $output       = [];
        $failed_skus  = [];
        $updatedSku   = [];
        $successCount = 0;
        $failureCount = 0;

        try {
            $this->logger->log(600, print_r($this->jsonHelper->serialize($data), true));
            
            $productData = $this->jsonHelper->unserialize($data);
            
            $productInfoCount = count($productData);
        
            $filterBy = 'sku';
            // based on default filter
            $countKey = ($productInfoCount - 2);
            if (array_key_exists($countKey, $productData)) {
                if (array_key_exists('filterby', $productData[$countKey])) {
                    $filterBy = $productData[$countKey]['filterby'];
                }
            }

            $is_price_update = 2; //by default 0 price won't update
            $countKey = ($productInfoCount - 1);
            if (array_key_exists($countKey, $productData)) {
                if (array_key_exists('is_price_update', $productData[$countKey])) {
                    $is_price_update = $productData[$countKey]['is_price_update'];
                }
            }
            
            $storeId = $this->storeManager->getStore()->getId();

            $storeIds = [];
            $stores   = $this->storeManager->getStores(true);
            foreach ($stores as $key => $value) {
                $storeIds[] = $value->getStoreId();
            }

            if (count($storeIds) == 0) {
                $storeIds[] = $storeId;
            }
        
            array_splice($productData, -2);

            $countKey = count($productData);
            
            list($output, $updatedSku, $successCount, $failureCount, $failed_skus) = $this->getProductResults(
                $productData,
                $output,
                $updatedSku,
                $successCount,
                $failureCount,
                $failed_skus,
                $filterBy,
                $is_price_update,
                $storeIds
            );

            $messages = [
                'sku'           => $output,
                'updated_sku'   => $updatedSku,
                'total_count'   => $countKey,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'failed_skus'   => $failed_skus,
            ];
        } catch (\Exception $e) {
            $failure_reason = ['Error' => $e->getMessage()];
            $messages       = ['failure_reason' => $failure_reason];
        }//end try

        $products->createProduct($messages);

        return $products;
    }//end createProducts()


    public function getProductResults($productData, $output, $updatedSku, $successCount, $failureCount, $failed_skus, $filterBy, $is_price_update, $storeIds)
    {
        foreach ($productData as $key => $data) {
            $flag = '';

            $filterValue = '';
            $sku         = $data['sku'];
        
            $selectedColumns = ['sku'];
            if (array_key_exists($filterBy, $data)) {
                $filterValue = $data[$filterBy];
            }

            if (!empty($filterValue)) {
                
                list($output, $updatedSku, $successCount, $failureCount, $failed_skus) = $this->createProductDetails($data, $updatedSku, $filterBy, $filterValue, $successCount, $failureCount, $storeIds, $output, $sku, $is_price_update, $selectedColumns, $flag, $failed_skus);
                
            } else {
                if ($sku != $flag) {
                    ++$failureCount;
                    $flag          = $sku;
                    $failed_skus[] = [
                        'sku'   => $sku,
                        'Error' => 'value is empty',
                    ];
                }
            }//end if
        }//end foreach

        return [
            $output,
            $updatedSku,
            $successCount,
            $failureCount,
            $failed_skus
        ];
    }


    public function createProductDetails($data, $updatedSku, $filterBy, $filterValue, $successCount, $failureCount, $storeIds, $output, $sku, $is_price_update, $selectedColumns, $flag, $failed_skus)
    {
        
        try {
            $collection = $this->getProductCollection($filterBy, $filterValue, $selectedColumns);

            if ($collection->getSize() != 0) {
                $sku = $collection->getData()[0]['sku'];
                $productId = $this->getProductId($collection->getData()[0]['sku']);
                $this->updateAttributesByStores($data, $storeIds, $productId);
                if (!in_array($sku, $updatedSku)) {
                    $updatedSku[] = $sku;
                }
                            
                if ($sku != $flag) {
                    ++$successCount;
                    $flag = $sku;
                }
                
            } else {
                if ($this->addProductData($data, $storeIds, $filterBy, $is_price_update)) {
                    if (!in_array($sku, $output)) {
                        $output[] = $sku;
                    }

                    if ($sku != $flag) {
                        ++$successCount;
                        $flag = $sku;
                    }
                }
            }//end if
        } catch (\Exception $e) {
            
            if ($sku != $flag) {
                ++$failureCount;
                $flag          = $sku;
                $failed_skus[] = [
                    'sku'   => $sku,
                    'Error' => $e->getMessage(),
                ];
            }
        }
        
        
        return [
            $output,
            $updatedSku,
            $successCount,
            $failureCount,
            $failed_skus
        ];
    }

    public function constructProductData($data, $is_price_update, $filterBy)
    {
        unset($data['store_id']);
        unset($data['store_ids']);
        unset($data['sku']);
        unset($data['attribute_set_id']);
        unset($data['type_id']);
    

        if (array_key_exists('delete_flag', $data)) {
            if ($data['delete_flag'] == "Y" || $data['status'] == 2) {
                $data['status'] = 2; // disable status
            }
        }

            
        if ($filterBy != 'sku') {
            if (array_key_exists($filterBy, $data)) {
                unset($data[$filterBy]);
            }
        }

        if (array_key_exists('price', $data)) {
            if (round($data['price']) == 0) {
                if ($is_price_update == 2) {
                    unset($data['price']);
                }
            }
        }

        $stockData = $this->getStockDetails($data);

        return [
            $data,
            $stockData
        ];
    }


    private function getStockDetails($data)
    {
        $stockData = [];
        if (array_key_exists('qty', $data)) {
            if ($data['qty'] > 0) {
                $stockStatus = 1;
            } else {
                $stockStatus = 0;
            }
            
            $stockData = [ 'qty' => $data['qty'], 'is_in_stock' => $stockStatus];
        }
        return $stockData;
    }

    public function getProductId($sku)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        $productId = $catalogProduct->getIdBySku($sku);
        return $productId;
    }

    public function updateProductDetails($sku, $value, $price)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        try {
            $productId = $catalogProduct->getIdBySku($sku);
            $catalogProduct->load($productId);
            $catalogProduct->setStoreId($value);
            $catalogProduct->setPrice($price);
            if ($catalogProduct->save()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getProductCollection($filterBy, $filterValue, $selectedColumns)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect($selectedColumns)->addFieldToFilter($filterBy, ['like' => $filterValue])->load();
        return $collection;
    }

    public function updateAttributesByStores($data, $storeIds, $productId)
    {
        try {
            if (!empty($data)) {
                $updateAttributes = [];
                if (array_key_exists('name', $data)) {
                    $updateAttributes['name'] = $data['name'];
                }

                if (array_key_exists('price', $data)) {
                    $updateAttributes['price'] = $data['price'];
                }
                
                if (array_key_exists('po_cost', $data)) {
                    $updateAttributes['po_cost'] = $data['po_cost'];
                }
            
                foreach ($storeIds as $storeId) {
                    $this->productAction->updateAttributes([$productId], $updateAttributes, $storeId);
                }

                //update qty
                if (array_key_exists('qty', $data)) {
                    $qty = $data['qty'];
                    $items = [ $productId => $qty];
                    $this->saveQty($productId, $qty);
                    //$this->updateProductQty($items)
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateProductData($productId, $data, $value, $stockData = [])
    {
        $catalogProduct = $this->catalogProductFactory->create();
        $catalogProduct->load($productId);
        $catalogProduct->setStoreId($value);
        
        if (!empty($data)) {
            unset($data['sku']);
            if (array_key_exists('name', $data)) {
                if ($catalogProduct->getName() == $data['name']) {
                    unset($data['name']);
                }
            }
            if (array_key_exists('status', $data)) {
                unset($data['status']);
            }
            $catalogProduct->addData($data);
        }

        if (!empty($stockData)) {
            $catalogProduct->setStockData($stockData);
        }

        if ($catalogProduct->save()) {
            return true;
        } else {
            return false;
        }
    }

    public function addProductData($data, $storeIds, $filterBy, $is_price_update)
    {
        $catalogProduct = $this->catalogProductFactory->create();
        $catalogProduct->setWebsiteIds([1]); //Default websites
        $catalogProduct->setData('sku', $data['sku']);
        if ($filterBy != 'sku') {
            if (array_key_exists($filterBy, $data)) {
                $catalogProduct->setData($filterBy, $data[$filterBy]);
            }
        }
        if (array_key_exists('name', $data)) {
            $catalogProduct->setName($data['name'].$data['sku']);
        }

        if (array_key_exists('attribute_set_id', $data)) {
            $catalogProduct->setAttributeSetId($data['attribute_set_id']);
        }
        
        $catalogProduct->setStoreIds($storeIds);
                            
        if (array_key_exists('status', $data)) {
            $catalogProduct->setStatus($data['status']);
        }
                            
        if (array_key_exists('type_id', $data)) {
            $catalogProduct->setTypeId($data['type_id']);
        }
                            
        if (array_key_exists('price', $data)) {
            if (round($data['price']) != 0) {
                $catalogProduct->setPrice($data['price']);
            } else {
                if ($is_price_update) {
                    $catalogProduct->setPrice($data['price']);
                }
            }
        }

        if (array_key_exists('short_description', $data)) {
            $catalogProduct->setShortDescription($data['short_description']);
        }


        /*Custom attributes*/
        if (array_key_exists('po_cost', $data)) {
            if (round($data['po_cost']) != 0) {
                $catalogProduct->setPoCost($data['po_cost']);
            }
        }

        if (array_key_exists('qty', $data)) {
            if ($data['qty'] > 0) {
                $stockStatus = 1;
            } else {
                $stockStatus = 0;
            }

            $catalogProduct->setStockData(
                [
                    'qty'         => $data['qty'],
                    'is_in_stock' => $stockStatus,
                ]
            );
        }

        if ($catalogProduct->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \Cloras\Base\Api\ProductResultsInterface
     */
    public function updateProductPrice($productInfo)
    {
        $products = $this->productFactory->create();

        $response = [];
        try {
            $productInfo = $this->jsonHelper->unserialize($productInfo);

            if (array_key_exists('sku', $productInfo) || array_key_exists('partnumber', $productInfo)) {
                $selectedColumns = ['sku'];
                $filterColumn    = 'sku';
                $filterValue     = $productInfo['sku'];
                $sku             = $productInfo['sku'];

                if (!empty($productInfo['partnumber'])) {
                    $selectedColumns = [
                        'sku',
                        'partnumber',
                    ];
                    $filterColumn    = 'partnumber';
                    $filterValue     = $productInfo['partnumber'];
                    list($response) = $this->getResponseProducts($filterColumn, $filterValue, $productInfo, $selectedColumns);
                }
            } else {
                $response[] = [
                    'status'  => 'failure',
                    'message' => 'SKU Key is not present',
                ];
            }//end if
        } catch (\Exception $e) {
            $response[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
            ];
        }//end try

        $products->setResponseMessage($response);

        return $products;
    }//end updateProductPrice()


    public function getResponseProducts($filterColumn, $filterValue, $productInfo, $selectedColumns)
    {

        $collection = $this->getProductCollection($filterColumn, $filterValue, $selectedColumns);

        $this->store->setCurrentStore('admin');

        $sku             = $productInfo['sku'];
        $collectionCount = count($collection);
        if ($collectionCount > 0) {
            $sku = $collection->getData()[0]['sku'];
            if ($sku) {
                $price = ((round($productInfo['UnitPrice']) != 0) ? $productInfo['UnitPrice'] : $productInfo['BaseUnitPrice']);
                if (round($price) != 0) {
                    if ($this->updateProductDetails($sku, 1, $price)) {
                        $response[] = [
                        'status'  => 'success',
                        'message' => $sku . ' updated',
                        ];
                    } else {
                        $response[] = [
                        'status'  => 'failure',
                        'message' => $sku . ' is not available',
                        ];
                    }
                } else {
                    $response[] = [
                    'status'  => 'success',
                    'message' => $sku . ' Price is 0 ',
                    ];
                }
            }
        }

        return [
            $response
        ];
    }

    /**
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateBulkInventory($data)
    {
        /* product Inventory Update*/
        $response = ['total_count' => 0, 'success_count' => 0, 'failure_count' => 0, 'failed_skus' => []];

        $productInfo = $this->jsonHelper->unserialize($data);
        $locationCode = '';
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($productInfo as $sku => $info) {
            if ($sku) {
                try {
                    $totalQty = 0;
                    foreach ($info as $location => $qty) {
                        $totalQty += (int) $qty;
                    }
                    $this->saveStockData($sku, $totalQty, $storeId);
                    $response['success_count'] += 1;
                } catch (\Exception $e) {
                    $response['failed_skus'][$sku] = $e->getMessage();
                    $response['failure_count'] += 1;
                }
                $response['total_count'] += 1;
            }
        }

        /**
         * @var \Cloras\Base\Api\Data\ResultsInterface
         */
        $results = $this->resultsFactory->create();

        $results->setResponse($response);

        return $results;
    }

    private function saveStockData($sku, $totalQty, $storeId)
    {
        $product = $this->productRepository->get($sku);
        $product->setStoreId($storeId);
        $product->setStockData(['qty' => $totalQty, 'is_in_stock' => $totalQty > 0]);
        $product->unsetData('media_gallery');
        $this->productRepository->save($product);
    }

    private function saveQty($productId, $qty)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        $stockItem->setData('qty', $qty);
        $stockItem->setData('is_in_stock', ($qty > 0 ) ? 1 : 0);
        $stockItem->save();
    }

    private function updateProductQty($items)
    {
        $this->productStock->correctItemsQty($items, $websiteId = 0, '+');
    }

    /**
     * @return \Cloras\Base\Api\Data\ProductResultsInterface
     */
    public function getNewProducts()
    {

        $filterAttribute['status'] = [
            'Pending',
            'Failed',
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status', $filterAttribute['status'], 'in')->create();

        $productIndexIds = $this->productIndexRepository->getProductIds($searchCriteria);

        $productData = [];
            $total_count = 0;
        $products    = $this->productFactory->create();
        if ($productIndexIds->getTotalCount()) {
            $productIds    = $productIndexIds->getItems();

        
            
            
            try {
                $productData    = [];
                
                $loadedProducts = [];

                $productCollection = $this->productCollectionFactory->create();
                if (count($productIds['all']) > 0) {
                        /** Apply filters here */
                        $productCollection->addAttributeToSelect(array('entity_id','sku','description','name','price','qty'));
                    $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds['all']));
                        /*$start = date('Y-m-d' . ' 00:00:00', $this->dateTime->timestamp());
                        $end = date('Y-m-d' . ' 23:59:59', $this->dateTime->timestamp());
                        $productCollection->addAttributeToFilter('created_at', array('from' => $start, 'to' => $end));*/
        
                    if ($productCollection->getSize() != 0) {
                        foreach ($productCollection as $product) {
                            $loadedProducts[] = $product->getId();
                            $productData[] = [
                            'sku'        => $product->getSku(),
                            'description' => $product->getDescription(),
                            'name' => $product->getName(),
                            'price' => $product->getPrice(),
                            'qty' => $product->getQty()
                        
                            ];
                            $total_count++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $productData[] = [
                'status'  => 'failure',
                'message' => $e->getMessage(),
                ];
            }//end try
        }
        $products->addProduct($productData);
        $products->setTotalProducts($total_count);
        if (!empty($loadedProducts)) {
                $productStatus = [
                    Product::STATUS_PENDING,
                    Product::STATUS_FAILED,
                ];
                $this->productIndexRepository->updateStatuses(
                    $loadedProducts,
                    $productStatus,
                    Product::STATUS_PROCESS
                );
        }
        return $products;
    }

    /**
     * @return \Cloras\Base\Api\ProductResultsInterface
     */
   
    public function updateNewProducts($data)
    {

        $response = ['total_count' => 0, 'success_count' => 0, 'failure_count' => 0, 'failed_skus' => []];
        $productData = $this->jsonHelper->unserialize($data);
        $productProcessStatus  = [Product::STATUS_PROCESS];
        array_splice($productData, -1);
        $syncedProducts = [];
        $unsyncedProducts = [];
        foreach ($productData as $key => $data) {
            if ($data['status'] == 'success') {
                try {
                    $product = $this->productRepository->get($data['sku']);
                    if (array_key_exists('item_id', $data)) {
                        $product->setCustomAttribute('p21_product_id', $data['item_id']);
                    }
                    if (array_key_exists('inv_mast_uid', $data)) {
                        $product->setCustomAttribute('inv_mast_uid', $data['inv_mast_uid']);
                    }
            $this->registry->register('ignore_product_update', 1);
                    $this->productRepository->save($product);
                    $response['success_count'] += 1;
                    $syncedProducts[] = $product->getId();
                } catch (\Exception $e) {
                    $response['failed_skus'][$data['sku']] = $e->getMessage();
                    $response['failure_count'] += 1;
                    $unsyncedProducts[] = $product->getId();
                }
            } else {
                $response['failed_skus'][$data['sku']] = $data['error'];
                $response['failure_count'] += 1;
            }
            $response['total_count'] += 1;
        }
        //print_r($productProcessStatus);print_r(Product::STATUS_COMPLETED);exit();
        if (!empty($syncedProducts)) {
            $this->productIndexRepository->updateStatuses(
                $syncedProducts,
                $productProcessStatus,
                Product::STATUS_COMPLETED
            );    
        }

            // Failed Productts
        if (!empty($unsyncedProducts)) {
            /*$this->productIndexRepository->updateStatuses(
                $unsyncedProducts,
                $productProcessStatus,
                Product::STATUS_FAILED
            );*/
        }
        $results = $this->resultsFactory->create();

        $results->setResponse($response);
    
        return $results;
    }
}//end class
