<?php
namespace DCKAP\Catalog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Price extends Action
{
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->priceHelper        = $priceHelper;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->stockState = $stockState;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        /** 1. get sku from product id
        2. make array with (uom, price, sku) for json response
         */
//        var_dump($this->getRequest()->getParams());die;
        $this->logger->info('ajax call started');
        $productIds = explode(',', $this->getRequest()->getParam('productIds'));
        $productsCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['sku','entity_id','qty','price'])
            ->addFieldToFilter('entity_id', ['in' => $productIds]);
        $resData = [];
        if ($productsCollection->getSize() > 0) {
            if ($this->customerSession->isLoggedIn()) {
                $sessionProductData = $this->customerSession->getProductData();
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('price_stock');
                if ($status) {
                    $skuArr = [];
                    foreach ($productsCollection as $product) {
                        if (!isset($sessionProductData[$product->getSku()])) {
                            $skuArr[] = $product->getSku();
                        }
                    }
                    if (!empty($skuArr)) {
                        $responseData = $this->clorasDDIHelper->getBulkPriceStock($integrationData, $skuArr);
                        $itemData = [];
                        $itemData = $sessionProductData;
                        if ($responseData && !empty($responseData) && !isset($responseData['isValid'])) {
                            foreach ($responseData as $data) {
                                $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                if ($sku != '') {
                                    $itemData[$sku] = $data;
                                }
                            }
                        }
                        $sessionProductData = $itemData;
                    }
                }
            } else {
                $sessionProductData = $this->customerSession->getGuestProductData();
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('guest_price_stock');
                if ($status) {
                    $skuArr = [];
                    foreach ($productsCollection as $product) {
                        if (!isset($sessionProductData[$product->getSku()])) {
                            $skuArr[] = $product->getSku();
                        }
                    }
                    if (!empty($skuArr)) {
                        $responseData = $this->clorasDDIHelper->getGuestBulkPriceStock($integrationData, $skuArr);
                        $itemData = [];
                        $itemData = $sessionProductData;
                        if ($responseData && !empty($responseData)) {
                            foreach ($responseData as $data) {
                                $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                if ($sku != '') {
                                    $itemData[$sku] = $data;
                                }
                            }
                        }
                        $sessionProductData = $itemData;
                    }
                }
            }
            foreach ($productsCollection as $product) {
                $arr = [];
                $arr['sku'] = $product->getSku();
                $arr['id'] = $product->getId();
                if (isset($sessionProductData[$product->getSku()]['prices']) && isset($sessionProductData[$product->getSku()]['lineItem'])) {
                    $productUOM = '';
                    $price = $sessionProductData[$product->getSku()]['prices']['netPrice'];
                    $arr['price'] = $this->priceHelper->currency($price, true, false);
                    $arr['price_nocurr'] =  $price;
                    $arr['uom'] = $sessionProductData[$product->getSku()]['lineItem']['uom']['uomCode'];
                    $arr['qty'] = $sessionProductData[$product->getSku()]['lineItem']['totalAvailable'];
                    $uomData = $sessionProductData[$product->getSku()]['lineItem']['uom'];
                    $this->logger->info(json_encode($uomData));
                    $productUOM .= '<select class="custom_uom" data-product-id="'.$product->getId().'" name="super_group_uom['.$product->getId().']" data-default-uom="'.$arr['uom'].'">';
                    foreach ($uomData['uomFactors'] as $uom) {
                        //Select the label
                        $pickupUOMLabel = $uom['altUomCode'];

                        $selected = ($uom['altUomCode'] == $arr['uom']) ? 'selected="selected"' : '';

                        $productUOM .= '<option value="' . $uom['altUomCode'] .'" data-price="' . $this->priceHelper->currency($uom['price'], true, false).'" data-org-price="' . $uom['price'] . '" ' . $selected . ' >' . $pickupUOMLabel . '</option>';
                    }
                    $productUOM .= '</select>';
                    $arr['customuom'] = $productUOM;
                } else {
                    $arr['price'] = $this->priceHelper->currency($product->getPrice(), true, false);
                    $arr['price_nocurr'] =  $product->getPrice();
                    $arr['uom'] = 'EA';
                    $arr['qty'] = $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                    $arr['customuom'] = 0;
                }
                $resData[$product->getId()] = $arr;
            }
        }
        // $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        // $response->setHeader('Content-type', 'text/plain');
        // $response->setContents($this->_jsonHelper->jsonEncode($resData));
        $this->logger->info('ajax call end');
        // return $response;

        return $resultJson->setData($resData);
    }
}
