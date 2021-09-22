<?php

namespace Cloras\Base\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $logger;

    private $customerSession;

    public static $prices = [];

    private $integrationCollection;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Framework\Filesystem\Io\File $io,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Catalog\Model\Product $productModel,
        \Cloras\Base\Model\ResourceModel\Integration\Collection $integrationCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger                = $logger;
        $this->dir                   = $dir;
        $this->io                    = $io;
        $this->customerSession       = $customerSession;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customer        = $customer;
        $this->curlClient            = $curl;
        $this->productModel          = $productModel;
        $this->integrationCollection = $integrationCollection;
        $this->scopeConfig = $scopeConfig;
    }//end __construct()

    public function makeDir($folderName = 'cloras')
    {
        try {
            $logFolder = BP . '/var/log/' . $folderName;
            if ($logFolder != '') {
                $this->io->mkdir($logFolder, 0777);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }//end makeDir()

    public function clientCurl($method, $serviceUrl, $headers, $requestData = '')
    {
       $response = '';
        try {
            $client = new \Zend_Http_Client();

            $client->setUri($serviceUrl);

            $client->setConfig(array('timeout'=>3000));


            if ($requestData) {
                $client->setRawData($requestData);
            }
                    
            $client->setHeaders($headers);

            $response = $client->request($method);

            return $response;
        } catch (\Zend\Http\Client\Exception $e) {
            return $e->getMessage();
        }
        return $response;
    }//end clientCurl()

    public function refreshToken($serviceUrl, $batchId)
    {
        try {
            $serviceUrl .= '/token/asap/' . $batchId;
            $response    = $this->clientCurl($method = 'GET', $serviceUrl, $headers = [], $requestData = []);
            if ($response->getStatus() == 200) {
                $results = json_decode($response->getBody(), true);
                if (!empty($results)) {
                    if (strtolower($results['status']) == 'success') {
                        $this->logger->info('Cloras response : ', $results);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Cloras response : ', (array) $e->getMessage());
        }
    }//end refreshToken()

    // Fetch Price from 3rd party services API
    public function fetchDynamicPrice($p21CustomerId, $integrationData, $items, $currentItemId, $qty = 1)
    {
        $prices = [];

        /* default product price */
        $payloadItem = [];
        foreach ($items as $item) {
            $prices[$item['item_id']]['product_id'] = $item['product_id'];
            if (!empty($item)) {
                $payloadItem[] = $item;
            } else {
                //$prices[$currentItemId] = 0;
            }
        }

//        $storeScope = $this->scopeInterface::SCOPE_STORE;

        $dynamic_pricing_url = 'clorasbase/dynamic_pricing/dynamic_pricing_url';

//        $baseURL = $this->scopeConfig->getValue($dynamic_pricing_url, $storeScope);
//        $apiPath = $this->scopeConfig->getValue('clorasbase/dynamic_pricing/api_path', $storeScope);
//        $token = $this->scopeConfig->getValue('clorasbase/dynamic_pricing/api_token', $storeScope);
        $baseURL = $this->scopeConfig->getValue($dynamic_pricing_url);
        $apiPath = $this->scopeConfig->getValue('clorasbase/dynamic_pricing/api_path');
        $token = $this->scopeConfig->getValue('clorasbase/dynamic_pricing/api_token');

        $serviceUrl = $baseURL . $apiPath;

        if(!empty($serviceUrl)){
            $payloadData = [];
           
            $payloadData['customer_id'] = $p21CustomerId;


            $itemData['products'] = [];

            

            $itemData['products'] = $payloadItem;

            $payload = array_merge($itemData, $payloadData);

            $requestData = json_encode($payload);

            $ContentType = 'application/json';

            

            $authValue = 'Bearer ' . $token;

                        $headers = [
                            'Content-Type'  => $ContentType,
                            'Authorization' => $authValue,
                        ];


                        $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                        if (is_object($response)) {
                       

                      
                        if ($response->getStatus() == 200) {
                        
                            $responseBody = $response->getBody();

                            $results = json_decode($response->getBody(), true);


                            if(array_key_exists('status', $results)){

                                if($results['status'] == 1){
                                    if(array_key_exists('data', $results)){
                                        if(!empty($results['data'])){
                                            foreach ($results['data'] as $result) {
                                                if (array_key_exists('unit_price', $result)) {

                                                    $unitPrice = (($result['unit_price'] != 0) ? $result['unit_price'] : 0);
                                                    $prices[$result['item_id']]['price'] = $unitPrice; 
                                                }
                                            }
                                        }
                                    }                                

                                }
                            }                             
                        }


                       


                        }



        }


        if (!empty($this->customerSession->getClorasCustomPrice())) {
                //$prices = $this->customerSession->getClorasCustomPrice() + $prices;
        }
        //$this->customerSession->setClorasCustomPrice($prices);    
    

        return $prices;

    
    }



    public function getProductItems($productIds, $sessionPrices, $qty, $filterBy)
    {
        $items = [];

        if (!empty($productIds)) {
            $selectedColumns = [
                'uom',
                'inv_mast_uid',
            ];
            if ($filterBy != 'sku') {
                array_push($selectedColumns, $filterBy);
            }

            $productsCollection = $this->productModel->getCollection()->addAttributeToSelect($selectedColumns)->addFieldToFilter('entity_id', ['in' => $productIds]);

            if ($productsCollection->getSize() > 0) {
        //echo $productsCollection->getSize();exit();
                foreach ($productsCollection as $product) {
                    $uom          = '';
                    $inv_mast_uid = '';
                    $itemId       = $product->getSku();
            $productId    = $product->getId();
                    if ($filterBy != 'sku') {
                        if (is_object($product->getCustomAttribute($filterBy))) {
                            $itemId = $product->getCustomAttribute($filterBy)->getValue();
                        }
                    }

                    if (is_object($product->getCustomAttribute('uom'))) {
                        $uom = $product->getAttributeText('uom');
                        if (empty($uom)) {
                            $uom = $product->getCustomAttribute('uom')->getValue();
                        }
                    }
            //print_r($sessionPrices);exit();
                    if (is_object($product->getCustomAttribute('inv_mast_uid'))) {
                        $inv_mast_uid = $product->getCustomAttribute('inv_mast_uid')->getValue();
                    }
                    //echo $uom." ".$inv_mast_uid." ".$itemId." ".$qty;
                    if (($uom != null || $inv_mast_uid != '') && ($itemId != '')
                        && ((!array_key_exists($itemId, $sessionPrices) || $qty > 1))
                    ) {
                        $items[$itemId] = [
                            'item_id'      => $itemId,
                'product_id'   => $productId,
                            'uom'          => $uom,
                            'qty'          => $qty,
                            'inv_mast_uid' => $inv_mast_uid,
                        ];
                    }
                }//end foreach
            }//end if
        }//end if
    //print_r($items);exit();
        return $items;
    }//end getProductItems()

    public function isServiceEnabled($type)
    {
        $flag        = 0;
        $serviceData = [];
        $p21_customerid = 0;
        $integrationData = [];
        $filterBy = 'sku';
        try {
            $customerSession = $this->customerSessionFactory->create();
            if ($customerSession->isLoggedIn()) {
                $id = $customerSession->getCustomerId();
                if (!$customerSession->getData('cloras_p21_customer_id')) {
                    $customer = $this->customer->load($id);
                    $customerSession->setData('cloras_p21_customer_id', $customer->getData('cloras_p21_customer_id'));
                }
                $p21_customerid = $customerSession->getData('cloras_p21_customer_id');

                $integrationData = $this->integrationCollection->getClorasIntegrationEntity($type);
                if (!empty($integrationData)) {
                    // price based on filter by

                    if (array_key_exists('prd_fltr_field', $integrationData)) {
                        $filterBy = $integrationData['prd_fltr_field'];
                    }

                    if ($integrationData['status'] == 1 && array_key_exists('batch_id', $integrationData)
                    && array_key_exists('cloras_url', $integrationData)
                    ) {
                        if (!empty($integrationData['cloras_url']) && !empty($integrationData['batch_id'])) {
                            if ($integrationData['token'] == '') {
                                $this->refreshToken($integrationData['cloras_url'], $integrationData['batch_id']);
                            }
                        }

                        $flag = 1;
                    }
                } else {
                    $prices = [];
                    $this->customerSession->setClorasCustomPrice($prices);
                }//end if
            }
        } catch (\Exception $e) {
            $this->logger->info('Cloras Error : ', (array)$e->getMessage());
        }//end try

        return [
            $flag,
            $p21_customerid,
            $integrationData,
            $filterBy
        ];
    }//end isServiceEnabled()


    public function getConfigValue($configPath){

//        $storeScope = $this->scopeInterface::SCOPE_STORE;

        $configValue = $this->scopeConfig->getValue($configPath);
        
        return $configValue;
    }
}//end class
