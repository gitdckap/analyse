<?php
/**
 * Copyright © DCKAP Inc. All rights reserved.
 */
namespace Cloras\DDI\Helper;

/**
 * Class Data
 * @package Cloras\DDI\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curlClient;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonHelper;

    /**
     * @var Service
     */
    protected $serviceHelper;

    /**
     * @var \DCKAP\Extension\Helper\Data
     */
    protected $extensionHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_catalogProductTypeConfigurable;

    /**
     * Data constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param Service $serviceHelper
     * @param \DCKAP\Extension\Helper\Data $extensionHelper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Cloras\DDI\Helper\Service $serviceHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
    )
    {
        $this->logger = $logger;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->curlClient = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->serviceHelper = $serviceHelper;
        $this->extensionHelper = $extensionHelper;
        $this->_checkoutSession = $_checkoutSession;
        $this->_regionFactory = $regionFactory;
        $this->addressRepository = $addressRepository;
        $this->scopeConfig = $scopeConfig;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
    }

    /**
     * @return bool
     */
    public function isB2B()
    {
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if ($configValue == "b2b") {
            return true;
        }
        return false;
    }

    /**
     * @param null $step
     * @return mixed|string
     */
    public function getBranchCode($step = null)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerEcommToken = $customerSession->getEcommtoken();
        if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
            $customerSessionData = $customerSession->getCustomData();
            if (isset($customerSessionData['branch']) && $customerSessionData['branch'] != '') {
                return $customerSessionData['branch'];
            }
        }
        $branch = $this->scopeConfig->getValue(
            'dckapextension/ddi_branch/branch_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if ($branch == "") {
            $branch = "01";
        }
        return $branch;
    }

    /**
     * @param $type
     * @return array
     */
    public function isServiceEnabled($type)
    {

        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $flag = 0;
        $integrationData = [];
        try {
            $serviceList = $this->serviceHelper->getServiceList();
            if (count($serviceList) && isset($serviceList[$type]) && $serviceList[$type]['status'] == '1') {
                $flag = 1;
                $integrationData = $serviceList[$type];
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Cloras Error : ', (array)$e->getMessage());
            }
        }

        return [
            $flag,
            $integrationData
        ];
    }

    /**
     * @param $method
     * @param $serviceUrl
     * @param $headers
     * @param string $requestData
     * @return \Magento\Framework\HTTP\Client\Curl|string|void
     */
    public function clientCurl($method, $serviceUrl, $headers, $requestData = '')
    {
        try {
            $baseURL = $this->serviceHelper->getClorasBaseUrl();
            $authorizeToken = $this->serviceHelper->getAuthorizeToken();
            $headers['Authorization'] = 'Bearer ' . $authorizeToken;
            $apiUrl = $baseURL.$serviceUrl;
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('service url ', (array)$apiUrl);
            }

            $response = '';
            $this->curlClient->setHeaders($headers);
            $this->curlClient->setOption(CURLOPT_IPRESOLVE,CURL_IPRESOLVE_V4);
            // $this->curlClient->setOption(CURLOPT_TCP_FASTOPEN,1);
            $this->curlClient->setOption( CURLOPT_ENCODING,  '');
            if (strtoupper($method) == 'POST') {
                $response = $this->curlClient->post($apiUrl, $requestData);
            } else {
                $response = $this->curlClient->get($serviceUrl, $requestData);
            }
            $response = $this->curlClient;

            return $response;
        } catch (\Exception $e) {
//            echo $e->getMessage();exit;
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Curl Error:' .$e->getMessage());
            }
        }
        return $response;
    }

    /**
     * Deprecated
     *
     * @param $method
     * @param $serviceUrl
     * @param $headers
     * @param string $requestData
     * @return \Magento\Framework\HTTP\Client\Curl|string|void
     */
    public function clientLamdaCurl($method, $serviceUrl, $headers, $requestData = '')
    {
        try {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('service url ', (array)$serviceUrl);
            }

            $response = '';
            $this->curlClient->setHeaders($headers);
            if (strtoupper($method) == 'POST') {
                $response = $this->curlClient->post($serviceUrl, $requestData);
            } else {
                $response = $this->curlClient->get($serviceUrl, $requestData);
            }
            $response = $this->curlClient;

            return $response;
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Curl Error:' .$e->getMessage());
            }
        }
        return $response;
    }

    /**
     * @param $integrationData
     * @param $emailId
     * @return bool|int
     */
    public function validateEcommUser($integrationData, $emailId)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started Validate User request');
        }

        if (isset($integrationData['token'])) {
            $serviceUrl = $integrationData['token'];
        } else {
            return false;
        }

        $payloadData = ["contactName" => $emailId];

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }

        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];

            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);

            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    if (!empty($results)) {
                        if (isset($results['data']['token'])) {
                            $this->customerSessionFactory->create()->setEcommtoken(['ecomm_token' => $results['data']['token']]);
//                            if (isset($results['data']['user'])) {
                            return $results['data'];
//                            } else {
//                                return 0;
//                            }
                        } elseif (isset($results['data'])) {
                            return $results['data'];
                        }
                    }
                } else {
                    return $results;
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
            return 'Error : ' . $e->getMessage();
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $emailId
     * @return bool|int
     */
    public function validateEcommUserSession($integrationData, $emailId)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerEcommToken = $customerSession->getEcommtoken();
        if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Started Validate Session request');
            }

            if (isset($integrationData['token'])) {
                $serviceUrl = $integrationData['token'];
            } else {
                return false;
            }
            $token = $customerEcommToken['ecomm_token'];

            $payloadData = [
                "contactName" => $emailId,
                "token" => $token
            ];

            $requestData = json_encode($payloadData);
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
            }

            try {
                $headers = [
                    'Content-Type' => 'application/json'
                ];

                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);

                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }

                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        $responseBody = $response->getBody();
                        $results = json_decode($responseBody, true);
                        if (!empty($results)) {
                            return $results['data'];
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Error : ' . $e->getMessage());
                }
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $sku
     * @return int
     */
    public function getPriceStock($integrationData, $sku)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerEcommToken = $customerSession->getEcommtoken();
        if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
            $customerSessionData = $customerSession->getCustomData();
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Started priceStock DDI request');
            }

            if (isset($integrationData['token'])) {
                $serviceUrl = $integrationData['token'];
            } else {
                return false;
            }

            $token = $customerEcommToken['ecomm_token'];
            $accountNumber = $customerSessionData['accountNumber'];
            $userId = $customerSessionData['userId'];

            $payloadData = [
                "token" => $token,
                "branch" => $this->getBranchCode(),
                "accountNumber" => $accountNumber,
                "userId" => $userId,
                "allWarehouse" => "N",
                "priceOnly" => "N",
                "stockOnly" => "N",
                "itemList" => [
                    [
                        "stockNum" => $sku,
                        "quantity" => "1"
                    ]
                ]
            ];
            /**
             * add condition to check the configuration and change allWarehouse value
             */
            if ($this->extensionHelper->getViewInventoryByLocation() == '3') {
                $payloadData["allWarehouse"] = "Y";
            }
            $requestData = json_encode($payloadData);
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
            }
            try {
                $headers = [
                    'Content-Type' => 'application/json'
                ];

                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data']['itemData'])) {

                                if (count($results['data']['itemData'])) {
                                    $itemData = array();
                                    $itemData = $customerSession->getProductData();
                                    foreach ($results['data']['itemData'] as $data) {
                                        $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                        if ($sku != '') {
                                            $itemData[$sku] = $data;
                                        }
                                    }
                                    $customerSession->setProductData($itemData);
                                }

                                return $results['data']['itemData'];
                            } else {
                                return 0;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Error : ' . $e->getMessage());
                }
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $sku
     * @return bool|int
     */
    public function getGuestPriceStock($integrationData, $sku)
    {
        $customerSession = $this->customerSessionFactory->create();
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started Guest priceStock DDI request');
        }

        if (isset($integrationData['token'])) {
            $serviceUrl = $integrationData['token'];
        } else {
            return false;
        }

        $payloadData = [
            "branch" => $this->getBranchCode(),
            "itemList" => [
                [
                    "stockNum" => $sku,
                    "quantity" => "1"
                ]
            ],
            "allWarehouse" => "N"
        ];
        if ($this->extensionHelper->getViewInventoryByLocation() == '3') {
            $payloadData["allWarehouse"] = "Y";
        }
        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    if (!empty($results)) {
                        if (isset($results['data']['itemData'])) {

                            $itemData = array();
                            $itemData = $customerSession->getGuestProductData();
                            foreach ($results['data']['itemData'] as $data) {
                                $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                if ($sku != '') {
                                    $itemData[$sku] = $data;
                                }
                            }
                            $customerSession->setGuestProductData($itemData);

                            return $results['data']['itemData'];
                        } else {
                            return 0;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $skuArr
     * @return int
     */
    public function getBulkPriceStock($integrationData, $skuArr)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerEcommToken = $customerSession->getEcommtoken();
        if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
            $customerSessionData = $customerSession->getCustomData();
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Started priceStock DDI request');
            }

            if (isset($integrationData['token'])) {
                $serviceUrl = $integrationData['token'];
            } else {
                return false;
            }

            $token = $customerEcommToken['ecomm_token'];
            $accountNumber = $customerSessionData['accountNumber'];
            $userId = $customerSessionData['userId'];

            $itemList = array();
            if ($skuArr && count($skuArr)) {
                foreach ($skuArr as $sku) {
                    $arr = array();
                    $arr["stockNum"] = $sku;
                    $arr["quantity"] = 1;
                    $itemList[] = $arr;
                }
            }

            $payloadData = [
                "token" => $token,
                "branch" => $this->getBranchCode(),
                "accountNumber" => $accountNumber,
                "userId" => $userId,
                "allWarehouse" => "N",
                "priceOnly" => "N",
                "stockOnly" => "N",
                "itemList" => $itemList
            ];
            if ($this->extensionHelper->getViewInventoryByLocation() == '3') {
                $payloadData["allWarehouse"] = "Y";
            }
            $requestData = json_encode($payloadData);
            
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
            }
            try {
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data']['itemData'])) {

                                if (count($results['data']['itemData'])) {
                                    $itemData = array();
                                    $itemData = $customerSession->getProductData();
                                    foreach ($results['data']['itemData'] as $data) {
                                        $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                        if ($sku != '') {
                                            $itemData[$sku] = $data;
                                        }
                                    }
                                    $customerSession->setProductData($itemData);
                                }
                                return $results['data']['itemData'];
                            } elseif (isset($results['data']['DDIResponse']['errorMessage'])) {
                                return $results['data']['DDIResponse'];
                            } else {
                                return 0;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Error : ' . $e->getMessage());
                }
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $skuArr
     * @return bool|int
     */
    public function getGuestBulkPriceStock($integrationData, $skuArr)
    {
        $customerSession = $this->customerSessionFactory->create();
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started Guest priceStock DDI request');
        }

        if (isset($integrationData['token'])) {
            $serviceUrl = $integrationData['token'];
        } else {
            return false;
        }

        $itemList = array();
        if ($skuArr && count($skuArr)) {
            foreach ($skuArr as $sku) {
                $arr = array();
                $arr["stockNum"] = $sku;
                $arr["quantity"] = 1;
                $itemList[] = $arr;
            }
        }
        $payloadData = [
            "branch" => $this->getBranchCode(),
            "itemList" => $itemList,
            "allWarehouse" => "N"
        ];
        if ($this->extensionHelper->getViewInventoryByLocation() == '3') {
            $payloadData["allWarehouse"] = "Y";
        }
        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    if (!empty($results)) {
                        if (isset($results['data']['itemData'])) {

                            $itemData = array();
                            $itemData = $customerSession->getGuestProductData();
                            foreach ($results['data']['itemData'] as $data) {
                                $sku = (isset($data['lineItem']['stockNum'])) ? $data['lineItem']['stockNum'] : '';
                                if ($sku != '') {
                                    $itemData[$sku] = $data;
                                }
                            }
                            $customerSession->setGuestProductData($itemData);

                            return $results['data']['itemData'];
                        } else {
                            return 0;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $shipto
     * @return int
     */
    public function getOrderpadItems($integrationData, $shipto)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started order pad DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId,
                ];
                if ($shipto && $shipto != '') {
                    $payloadData['shipNumber'] = $shipto;
                }
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data'])) {
                                return $results['data'];
                            } else {
                                return $results;
                            }
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
            return 'Error : ' . $e->getMessage();
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @return int
     */
    public function getShiptoItems($integrationData)
    {
        $customerSession = $this->customerSessionFactory->create();
        $customerEcommToken = $customerSession->getEcommtoken();
        if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
            $customerSessionData = $customerSession->getCustomData();
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Started Ship-to DDI request');
            }

            if (isset($integrationData['token'])) {
                $serviceUrl = $integrationData['token'];
            } else {
                return false;
            }

            $token = $customerEcommToken['ecomm_token'];
            $accountNumber = $customerSessionData['accountNumber'];
            $userId = $customerSessionData['userId'];

            $payloadData = [
                "branch" => $this->getBranchCode(),
                "token" => $token,
                "accountNumber" => $accountNumber,
                "userId" => $userId
            ];
            $requestData = json_encode($payloadData);
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
            }
            try {
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data']['shipTos'])) {
                                return $results['data']['shipTos'];
                            } else {
                                return 0;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Error : ' . $e->getMessage());
                }
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $email
     * @return int
     */
    public function shipToInsert($integrationData, $email, $websiteId)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started Validate User request');
        }

        $serviceUrl = $integrationData['token'];
        $payloadData = [
            "branch" => $this->getBranchCode(),
            "contactName" => $email,
            "website_id" => $websiteId
        ];

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }

        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];

            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);

            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }

                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if (!empty($results)) {
                        return 1;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $email
     * @param $websiteId
     * @return int
     */
    public function orderApprovalInsert($integrationData, $email, $websiteId)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started order Approval Insert request');
        }

        $serviceUrl = $integrationData['token'];
        $payloadData = [
            "branch" => $this->getBranchCode(),
            "contactName" => $email,
            "website_id" => $websiteId
        ];

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }

        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];

            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);

            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }

                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if (!empty($results)) {
                        return 1;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $filterData
     * @return int
     */
    public function getOrderList($integrationData, $filterData)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started OrderList DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                if (isset($filterData['startDate'])) {
                    $startDate = $filterData['startDate'];
                } else {
                    $startDate = date('m/d/y', strtotime('-1 year'));
                }
                if (isset($filterData['endDate'])) {
                    $endDate = $filterData['endDate'];
                } else {
                    $endDate = date('m/d/y');
                }
                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId,
                    "dateRange" =>[
                        "startDate" => $startDate,
                        "endDate" => $endDate
                    ]
                ];
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (isset($results['data'])) {
                            return $results['data'];
                        } else {
                            return $results;
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
            return 'Error : ' . $e->getMessage();
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @return int
     */
    public function getCustLedger($integrationData)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started CustLedger DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId
                ];
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data']['receivableAging'])) {
                                return $results['data']['receivableAging'];
                            } else {
                                return $results['data'];
                            }
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
            return 'Error : ' . $e->getMessage();
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $orderNumber
     * @return int
     */
    public function getOrderDetail($integrationData, $orderNumber)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started OrderDetail DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId,
                    "orderNumber" => $orderNumber
                ];
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (!empty($results)) {
                            if (isset($results['data'])) {
                                return $results['data'];
                            } else {
                                return $results;
                            }
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
            return 'Error : ' . $e->getMessage();
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $filterData
     * @return int
     */
    public function getInvoiceList($integrationData, $filterData)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started InvoiceList DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                if (isset($filterData['startDate'])) {
                    $startDate = $filterData['startDate'];
                } else {
                    $startDate = date('m/d/y', strtotime('-1 year'));
                }
                if (isset($filterData['endDate'])) {
                    $endDate = $filterData['endDate'];
                } else {
                    $endDate = date('m/d/y');
                }
                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId,
                    "dateRange" =>[
                        "startDate" => $startDate,
                        "endDate" => $endDate
                    ]
                ];
                if (isset($filterData['openOnly'])) {
                    $payloadData["openOnly"] = "Y";
                }
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (isset($results['data'])) {
                            return $results['data'];
                        } else {
                            return $results;
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Error : ' . $e->getMessage());
        }
        return 'Error : ' . $e->getMessage();
    }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $invoiceNumber
     * @param bool $isPdf
     * @return int
     */
    public function getInvoiceDetail($integrationData, $invoiceNumber, $isPdf = false)
    {
        try {
            $customerSession = $this->customerSessionFactory->create();
            $customerEcommToken = $customerSession->getEcommtoken();
            if ($customerEcommToken && isset($customerEcommToken['ecomm_token'])) {
                $customerSessionData = $customerSession->getCustomData();
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('Started InvoiceDetail DDI request');
                }

                if (isset($integrationData['token'])) {
                    $serviceUrl = $integrationData['token'];
                } else {
                    return false;
                }

                $token = $customerEcommToken['ecomm_token'];
                $accountNumber = $customerSessionData['accountNumber'];
                $userId = $customerSessionData['userId'];

                $payloadData = [
                    "branch" => $this->getBranchCode(),
                    "token" => $token,
                    "accountNumber" => $accountNumber,
                    "userId" => $userId,
                    "invoiceNumber" => $invoiceNumber,
                    "pdf" => "N"
                ];
                if ($isPdf) {
                    $payloadData["pdf"] = "Y";
                }
                $requestData = json_encode($payloadData);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                }
                $headers = [
                    'Content-Type' => 'application/json'
                ];
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                if (is_object($response)) {
                    $responseBody = $response->getBody();
                    $results = json_decode($responseBody, true);
                    if ($this->extensionHelper->getIsLogger()) {
                        $this->logger->info('API Response ', (array)$results);
                    }
                    if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                        if (isset($results['data'])) {
                            return $results['data'];
                        } else {
                            return $results;
                        }
                    } else {
                        if (isset($results['message'])) {
                            return $results['message'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Error : ' . $e->getMessage());
        }
        return 'Error : ' . $e->getMessage();
    }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $customer
     * @return int
     */
    public function createCustomer($integrationData, $customer)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started create customer DDI request');
        }

        $serviceUrl = $integrationData['token'];

        $billingAddress = [];
        if ($customer->getAddresses() && count($customer->getAddresses())) {
            foreach ($customer->getAddresses() as $address) {
                if ($customer->getDefaultBilling() == $address->getId()) {
                    $billingAddress['billAddress1'] = implode(', ', $address->getStreet());
                    $billingAddress['billCity'] = $address->getCity();
                    $billingAddress['billState'] = $address->getRegion()->getRegionCode();
                    $billingAddress['billPostCode'] = $address->getPostcode();
                    $billingAddress['billCountry'] = $address->getCountryId();
                    $billingAddress['billPhone'] = $address->getTelephone();
                    $billingAddress['billCompanyName'] = $address->getCompany();
                    $billingAddress['billFax'] = '';
                    $billingAddress['billTax'] = '';
                }
            }
        }

        $payloadData = [
            "branch" => $this->getBranchCode(),
            "contactName" => $customer->getEmail(),
            "firstName" => $customer->getFirstname(),
            "lastName" => $customer->getLastname(),
            "email" => $customer->getEmail(),
            "billCompanyName" => $billingAddress['billCompanyName'],
            "billAddress1" => $billingAddress['billAddress1'],
            "billAddress2" => "",
            "billAddress3" => "",
            "billCity" => $billingAddress['billCity'],
            "billState" => $billingAddress['billState'],
            "billPostCode" => $billingAddress['billPostCode'],
            "billCountry" => $billingAddress['billCountry'],
            "billPhone" => $billingAddress['billPhone'],
            "billFax" => $billingAddress['billFax'],
            "billTax" => $billingAddress['billTax']
        ];
        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    if (!empty($results)) {
                        if (isset($results['data'])) {
                            return $results['data'];
                        } else {
                            return 0;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $checkoutData
     * @return int
     */
    public function submitOrder($integrationData, $checkoutData)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started Submit Order DDI request');
        }
        $serviceUrl = $integrationData['token'];

        $customerSession = $this->customerSessionFactory->create();
        $customerSessionData = $customerSession->getCustomData();
        $customerEcommToken = $customerSession->getEcommtoken()['ecomm_token'];
        $accountNumber = $customerSessionData['accountNumber'];
        $userId = $customerSessionData['userId'];
        $userName = $customerSessionData['userName'];
        $firstName = $customerSessionData['firstName'];
        $lastName = $customerSessionData['lastName'];
        $email = $customerSessionData['email'];

        $branch = $checkoutData['branch'];
        if ($branch == '') {
            $branch = $this->getBranchCode('checkout');
        }
        $payloadData = [
            "token" => $customerEcommToken,
            "branch" => $branch,
            "accountNumber" => $accountNumber,
            "user" => [
                "userId" => $userId,
                "userName" => $userName,
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $email
            ],
            "purchaseOrder" => $checkoutData['purchase_order_number'],
            "jobName" => "",
            "specialInstructions" => $checkoutData['special_instructions'],
            "mobilePhone" => $checkoutData['delivery_contact_no'],
            "freightCharge" => (string)number_format($checkoutData['shippingAmount'], 4),
            "emailCC" => $email,
            "shipMethod" => $checkoutData['shippingMethod'],
            "billAttention" => $firstName." ".$lastName,
            "shipAddress" => $checkoutData['shipAddress'],
            "lineItems" => $checkoutData['lineItems']
        ];
        if (isset($checkoutData['paymentDetails'])) {
            $paymentDetails = $checkoutData['paymentDetails'];
            $payloadData['cashTendered'] = $paymentDetails['cashTendered'];
            $payloadData['methodOfPayment'] = $paymentDetails['methodOfPayment'];
            $payloadData['creditCardNumber'] = $paymentDetails['creditCardNumber'];
            $payloadData['creditCardName'] = $paymentDetails['creditCardName'];
            $payloadData['creditCardTransCode'] = $paymentDetails['creditCardTransCode'];
            $payloadData['creditCardApprovalCode'] = $paymentDetails['creditCardApprovalCode'];
            $payloadData['creditCardAuthCode'] = $paymentDetails['creditCardAuthCode'];
            $payloadData['creditCardType'] = $paymentDetails['creditCardType'];
            //$payloadData['creditCardApprovalCode'] = ' ';
            //$payloadData['creditCardAuthCode'] = ' ';
            //$payloadData['creditCardType'] = ' ';
        }

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ++++ ', (array)$results);
                }
                if ($response->getStatus() == 100 || $response->getStatus() == 200) {
                    if (!empty($results)) {
                        if (isset($results['data'])) {
                            return $results;
                        } else {
                            return $results;
                        }
                    }
                } else {
                    return $results;
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $checkoutData
     * @param $customerData
     * @return int
     */
    public function submitPendingOrder($integrationData, $checkoutData, $customerData)
    {
        $returnRes = [];
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started pending order DDI request');
        }
        $serviceUrl = $integrationData['token'];
        list($status, $validateUserIntegrationData) = $this->isServiceEnabled('validate_user');
        if ($status) {
            $users = $this->validateEcommUser($validateUserIntegrationData, $customerData['email']);
            if (is_string($users)) {
                $returnRes['status'] = 0;
                $returnRes['message'] = $users;
                return $returnRes;
            } elseif (isset($users['isValid']) && $users['isValid'] == 'no') {
                $returnRes['status'] = 0;
                $returnRes['message'] = $users['errorMessage'];
                return $returnRes;
            } elseif (isset($users['user']) && count($users['user'])) {
            // if (isset($users['user']) && count($users['user'])) {
                foreach ($users['user'] as $user) {
                    if ($user['accountNumber'] == $customerData['account_number'] && $user['userId'] == $customerData['user_id']) {
                        $customerEcommToken = $user['token'];
                        $accountNumber = $user['accountNumber'];
                        $userId = $user['userId'];
                        $userName = $user['userName'];
                        $firstName = $user['firstName'];
                        $lastName = $user['lastName'];
                        $email = $user['email'];

                        $branch = $checkoutData['branch'];
                        if ($branch == '') {
                            $branch = $this->getBranchCode('checkout');
                        }
                        $payloadData = [
                            "token" => $customerEcommToken,
                            "branch" => $branch,
                            "accountNumber" => $accountNumber,
                            "user" => [
                                "userId" => $userId,
                                "userName" => $userName,
                                "firstName" => $firstName,
                                "lastName" => $lastName,
                                "email" => $email
                            ],
                            "purchaseOrder" => $checkoutData['purchase_order_number'],
                            "jobName" => "",
                            "specialInstructions" => $checkoutData['special_instructions'],
                            "mobilePhone" => $checkoutData['delivery_contact_no'],
                            "freightCharge" => (string)number_format($checkoutData['shippingAmount'], 4),
                            "emailCC" => $email,
                            "shipMethod" => $checkoutData['shippingMethod'],
                            "billAttention" => $firstName." ".$lastName,
                            "shipAddress" => $checkoutData['shipAddress'],
                            "lineItems" => $checkoutData['lineItems']
                        ];
                        if (isset($checkoutData['paymentDetails'])) {
                            $paymentDetails = $checkoutData['paymentDetails'];
                            $payloadData['cashTendered'] = $paymentDetails['cashTendered'];
                            $payloadData['methodOfPayment'] = $paymentDetails['methodOfPayment'];
                            $payloadData['creditCardNumber'] = $paymentDetails['creditCardNumber'];
                            $payloadData['creditCardName'] = $paymentDetails['creditCardName'];
                            $payloadData['creditCardTransCode'] = $paymentDetails['creditCardTransCode'];
                            $payloadData['creditCardApprovalCode'] = $paymentDetails['creditCardApprovalCode'];
                            $payloadData['creditCardAuthCode'] = $paymentDetails['creditCardAuthCode'];
                            $payloadData['creditCardType'] = $paymentDetails['creditCardType'];
                        }

                        $requestData = json_encode($payloadData);
                        if ($this->extensionHelper->getIsLogger()) {
                            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
                        }
                        try {
                            $headers = [
                                'Content-Type' => 'application/json'
                            ];
                            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
                            if (is_object($response)) {
                                $responseBody = $response->getBody();
                                $results = json_decode($responseBody, true);
                                if ($this->extensionHelper->getIsLogger()) {
                                    $this->logger->info('API Response ', (array)$results);
                                }
                                if ($response->getStatus() == 100 || $response->getStatus() == 200) {
                                    if (!empty($results)) {
                                        if (isset($results['data'])) {
                                            return $results;
                                        } else {
                                            return $results;
                                        }
                                    }
                                } else {
                                    return $results;
                                }
                            }
                        } catch (\Exception $e) {
                            if ($this->extensionHelper->getIsLogger()) {
                                $this->logger->info('Error : ' . $e->getMessage());
                            }
                            $returnRes['status'] = 0;
                            $returnRes['message'] = 'Error : ' . $e->getMessage();
                            return $returnRes;
                        }
                    } else {
                        $returnRes['status'] = 0;
                        $returnRes['message'] = $customerData['account_number'] . ' Account number contact not found in ERP.';
                        return $returnRes;
                    }
                }
            } else {
                return $users;
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $checkoutData
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkoutReview($integrationData, $checkoutData)
    {
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started checkout review DDI request');
        }

        $serviceUrl = $integrationData['token'];

        $customerSession = $this->customerSessionFactory->create();
        $customerSessionData = $customerSession->getCustomData();
        $customerEcommToken = $customerSession->getEcommtoken()['ecomm_token'];
        $accountNumber = $customerSessionData['accountNumber'];
        $userId = $customerSessionData['userId'];
        $userName = $customerSessionData['userName'];
        $firstName = $customerSessionData['firstName'];
        $lastName = $customerSessionData['lastName'];
        $email = $customerSessionData['email'];
	    try {
        $shippingAddress = $this->_checkoutSession->getQuote()->getShippingAddress()->getData();
        $shipperRegion = $this->_regionFactory->create()->load($shippingAddress['region_id']);
        $shipperRegionCode =$shipperRegion->getCode();

        $shipToNumber = "999999999";
        if (isset($shippingAddress['customer_address_id']) && $shippingAddress['customer_address_id'] != null) {
            $addressId = (int)$shippingAddress['customer_address_id'];
            $shipToAddress = $this->addressRepository->getById($addressId);
            $ddiShipToNumber = $shipToAddress->getCustomAttribute('ddi_ship_number');
            if ($ddiShipToNumber) {
                $shipToNumber = $ddiShipToNumber->getValue();
            }
        }

        $mQuoteItems = $checkoutData['m_quote_items'];
        $shippingMethod = $shippingAddress['shipping_method'];
        $lineItemData = array();

        if ($mQuoteItems && count((array)$mQuoteItems)) {
            foreach ($mQuoteItems as $quoteItem) {
                $uom = 'EA';
                if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
                    $additionalOption = (array) $this->jsonHelper->unserialize($additionalOptions->getValue());
                    $uom = $additionalOption['custom_uom']['value'];
                }
                $data = array();
                $data['stockNum'] = $quoteItem->getSku();
                $data['qty'] = (string)$quoteItem->getQty();
                $data['uom'] = $uom;
                $data['price'] = (string)$quoteItem->getPrice();
                $data['mfgNum'] = '';
                $productName = preg_replace('/[^A-Za-z0-9_., -]/', '', $quoteItem->getName());
                $data['description'] = $productName;

                /* commented existing $0 lineitem removal code */
                /*if ($quoteItem->getPrice() > 0) {
//                if ($quoteItem->getPrice() > 0 && $quoteItem->getProductType() != 'configurable') {
                    $lineItemData[] = $data;
                }*/
                /* Added new code to allow $0 lineitem */
                $allowZero = $this->extensionHelper->getProceedToCheckout();
                if ($allowZero == '0') {
                    if ($quoteItem->getParentItem() != null) {
                        $childId = $quoteItem->getProductId();
                        $parentByChild = [];
                        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($childId);
                        $childId = "";
                        if (isset($parentByChild[0])) {
                            if ($quoteItem->getPrice() > 0) {
                                $lineItemData[] = $data;
                            }
                        }
                    } else {
                        $lineItemData[] = $data;
                    }
                } else {
                    if ($quoteItem->getPrice() > 0) {
                        $lineItemData[] = $data;
                    }
                }
            }
        }

        $street = $this->_checkoutSession->getQuote()->getShippingAddress()->getStreet();

        $payloadData = [
            "token" => $customerEcommToken,
            "branch" => $this->getBranchCode('checkout'),
            "accountNumber" => $accountNumber,
            "user" => [
                "userId" => $userId,
                "userName" => $userName,
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $email
            ],
            "purchaseOrder" => (isset($checkoutData['po_number']) ? $checkoutData['po_number'] : ''),
            "jobName" => "",
            "mobilePhone" => "",
            "specialInstructions" => "",
            "freightCharge" => (string)number_format($shippingAddress['shipping_amount'], 4),
            "emailCC" => $email,
            "shipMethod" => $shippingMethod,
            "billAttention" => $firstName." ".$lastName,
            "shipAddress" => [
                "shipId" => $shipToNumber,
                "shipCompanyName" => ($shippingAddress['company']) ? $shippingAddress['company'] : "",
                "shipAddress1" => (isset($street[0])) ? $street[0] : "",
                "shipAddress2" => (isset($street[1])) ? $street[1] : "",
                "shipAddress3" => (isset($street[2])) ? $street[2] : "",
                "shipCity" => $shippingAddress['city'],
                "shipState" => $shipperRegionCode,
                "shipPostCode" => $shippingAddress['postcode'],
                "shipCountry" => $shippingAddress['country_id'],
                "shipPhone" =>$shippingAddress['telephone'],
                "shipFax" => "",
                "shipAttention" => $shippingAddress['firstname']." ".$shippingAddress['lastname'],
                "quoteRequest" => "N",
                "validateOnly" => "Y"
            ],
            "lineItems" => [
                "itemData" => $lineItemData
            ]
        ];
        if ($checkoutData['review_type'] == 'quote') {
            $payloadData['shipAddress']['quoteRequest'] = "Y";
        }

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }

            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                if ($response->getStatus() == 100 || $response->getStatus() == 200) {
                    if (!empty($results)) {
                        if (isset($results['data'])) {
                            return $results;
                        } else {
                            return 0;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $checkoutData
     * @param $type
     * @return bool|int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function UpdateErpPrice($integrationData, $checkoutData, $type)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dataaa.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("checkoutReview");
        $logger->info(print_r((array)json_decode($checkoutData['shipping_address']), true));
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('Started checkout review DDI request');
        }
        $lamda = false;
        if (isset($integrationData['token'])) {
            $serviceUrl = $integrationData['token'];
        } elseif (isset($integrationData['url'])) {
            $serviceUrl = $integrationData['url'];
            $lamda = true;
        } else {
            return false;
        }

        $customerSession = $this->customerSessionFactory->create();
        $customerSessionData = $customerSession->getCustomData();
        $customerEcommToken = $customerSession->getEcommtoken()['ecomm_token'];
        $accountNumber = $customerSessionData['accountNumber'];
        $userId = $customerSessionData['userId'];
        $userName = $customerSessionData['userName'];
        $firstName = $customerSessionData['firstName'];
        $lastName = $customerSessionData['lastName'];
        $email = $customerSessionData['email'];

        $payloadData = [];
        if ($type == 'checkout') {
            $payloadData = [
                "token" => $customerEcommToken,
                "branch" => $this->getBranchCode('checkout'),
                "accountNumber" => $accountNumber,
                "user" => [
                    "userId" => $userId,
                    "userName" => $userName,
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "email" => $email
                ],
                "purchaseOrder" => $checkoutData['purchase_order_number'],
                "jobName" => "",
                "specialInstructions" => $checkoutData['special_instructions'],
                "mobilePhone" => $checkoutData['delivery_contact_no'],
                "freightCharge" => $checkoutData['shippingAmount'],
                "emailCC" => $email,
                "shipMethod" => $checkoutData['shippingMethod'],
                "billAttention" => $firstName." ".$lastName,
                "shipAddress" => $checkoutData['shipAddress'],
                "lineItems" => $checkoutData['lineItems']
            ];
            if (isset($checkoutData['paymentDetails'])) {
                $paymentDetails = $checkoutData['paymentDetails'];
                $payloadData['cashTendered'] = $paymentDetails['cashTendered'];
                $payloadData['methodOfPayment'] = $paymentDetails['methodOfPayment'];
                $payloadData['creditCardNumber'] = $paymentDetails['creditCardNumber'];
                $payloadData['creditCardName'] = $paymentDetails['creditCardName'];
                $payloadData['creditCardTransCode'] = $paymentDetails['creditCardTransCode'];
                $payloadData['creditCardApprovalCode'] = $paymentDetails['creditCardApprovalCode'];
                $payloadData['creditCardAuthCode'] = $paymentDetails['creditCardAuthCode'];
                $payloadData['creditCardType'] = $paymentDetails['creditCardType'];
            }
        } else {
            //$shippingAddress = json_decode($checkoutData['shipping_address']);
            $shippingAddress = (array)json_decode($checkoutData['shipping_address']);
            $logger->info(print_r($shippingAddress['regionId'], true));

            $shipperRegion = $this->_regionFactory->create()->load($shippingAddress['regionId']);
            $shipperRegionCode =$shipperRegion->getCode();

            $shipToNumber = "999999999";
            if (isset($shippingAddress['customerAddressId']) && $shippingAddress['customerAddressId'] != null) {
                $addressId = (int)$shippingAddress['customerAddressId'];
                $shipToAddress = $this->addressRepository->getById($addressId);
                $ddiShipToNumber = $shipToAddress->getCustomAttribute('ddi_ship_number');

                if ($ddiShipToNumber) {
                    $shipToNumber = $ddiShipToNumber->getValue();
                }
            }

            //$quoteItems = json_decode($checkoutData['quote_items']);
            //$shipping = json_decode($checkoutData['shipping_method']);

            $mQuoteItems = $checkoutData['m_quote_items'];
            $shippingMethod = "";//$shipping->carrier_code.'_'.$shipping->method_code;
            $lineItemData = array();

            //if ($quoteItems && count($quoteItems)) {
            if ($mQuoteItems && count((array)$mQuoteItems)) {
                /*foreach ($quoteItems as $quoteItem) {
                    $data = array();
                    $data['stockNum'] = $quoteItem->sku;
                    $data['qty'] = $quoteItem->qty;
                    $data['uom'] = 'EA';
                    $data['price'] = $quoteItem->price;
                    $data['mfgNum'] = '';
                    $data['description'] = $quoteItem->name;
                    $lineItemData[] = $data;
                }*/
                foreach ($mQuoteItems as $quoteItem) {
                    $uom = 'EA';
                    if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
                        $additionalOption = (array) $this->jsonHelper->unserialize($additionalOptions->getValue());
                        $uom = $additionalOption['custom_uom']['value'];
                    }
                    $data = array();
                    $data['stockNum'] = $quoteItem->getSku();
                    $data['qty'] = (string)$quoteItem->getQty();
                    $data['uom'] = $uom;
                    $data['price'] = (string)$quoteItem->getPrice();
                    $data['mfgNum'] = '';
                    $productName = preg_replace('/[^A-Za-z0-9_., -]/', '', $quoteItem->getName());
//                    $data['description'] = str_replace(array('"', '\\', '®', '/', '°'), '', $quoteItem->getName());
                    $data['description'] = $productName;

                    /* commented existing $0 lineitem removal code */
                    /*if ($quoteItem->getPrice() > 0) {
//                    if ($quoteItem->getPrice() > 0 && $quoteItem->getProductType() != 'configurable') {
                        $lineItemData[] = $data;
                    }*/
                    /* Added new code to allow $0 lineitem */
                    $allowZero = $this->extensionHelper->getProceedToCheckout();
                    if ($allowZero == '0') {
                        if ($quoteItem->getParentItem() != null) {
                            $childId = $quoteItem->getProductId();
                            $parentByChild = [];
                            $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($childId);
                            $childId = "";
                            if (isset($parentByChild[0])) {
                                if ($quoteItem->getPrice() > 0) {
                                    $lineItemData[] = $data;
                                }
                            }
                        } else {
                            $lineItemData[] = $data;
                        }
                    } else {
                        if ($quoteItem->getPrice() > 0) {
                            $lineItemData[] = $data;
                        }
                    }
                }
            }
            $company_name= ($shippingAddress['company'] ?$shippingAddress['company']:"");
            $street = (isset($shippingAddress['street'])) ? $shippingAddress['street'] : [] ;
            $payloadData = [
                "token" => $customerEcommToken,
                "branch" => $this->getBranchCode('checkout'),
                "accountNumber" => $accountNumber,
                "user" => [
                    "userId" => $userId,
                    "userName" => $userName,
                    "firstName" => $firstName,
                    "lastName" => $lastName,
                    "email" => $email
                ],
                "purchaseOrder" => (isset($checkoutData['po_number']) ? $checkoutData['po_number'] : ''),
                "jobName" => "",
                "specialInstructions" => "",
                "freightCharge" => "",//$shipping->amount,
                "emailCC" => $email,
                "shipMethod" => $shippingMethod,
                "billAttention" => $firstName." ".$lastName,
                "shipAddress" => [
                    "shipId" => $shipToNumber,
                    "shipCompanyName" => $company_name,
                    "shipAddress1" => (isset($street[0])) ? $street[0] : "",
                    "shipAddress2" => (isset($street[1])) ? $street[1] : "",
                    "shipAddress3" => (isset($street[2])) ? $street[2] : "",
                    "shipCity" => $shippingAddress['city'],//$shippingAddress->city,
                    "shipState" => $shipperRegionCode,//$shippingAddress->regionCode,
                    "shipPostCode" => $shippingAddress['postcode'],//$shippingAddress->postcode,
                    "shipCountry" => $shippingAddress['countryId'],//$shippingAddress->countryId,
                    "shipPhone" =>$shippingAddress['telephone'], //$shippingAddress->telephone,
                    "shipFax" => "",
                    "shipAttention" => $firstName." ".$lastName,
                    "quoteRequest" => "N",
                    "validateOnly" => "Y"
                ],
                "lineItems" => [
                    "itemData" => $lineItemData
                ]
            ];
            if ($checkoutData['review_type'] == 'quote') {
                $payloadData['shipAddress']['quoteRequest'] = "Y";
            }
        }

        $requestData = json_encode($payloadData);
        if ($this->extensionHelper->getIsLogger()) {
            $this->logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        }
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            if ($lamda) {
                $response = $this->clientLamdaCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            } else {
                $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            }
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                if ($this->extensionHelper->getIsLogger()) {
                    $this->logger->info('API Response ', (array)$results);
                }
                if ($response->getStatus() == 100 || $response->getStatus() == 200) {
                    if (!empty($results)) {
                        if (isset($results['data'])) {
                            return $results;
                        } else {
                            return 0;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->extensionHelper->getIsLogger()) {
                $this->logger->info('Error : ' . $e->getMessage());
            }
        }
        return 0;
    }

    /**
     * @param $integrationData
     * @param $data
     * @return int
     */
    public function submitPayment($integrationData, $data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pay_invoice.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("--------------------------------------------------");
        $logger->info('Started submit payment DDI request');

        if (isset($integrationData['token'])) {
            $serviceUrl = $integrationData['token'];
        } else {
            return false;
        }

        $customerSession = $this->customerSessionFactory->create();
        $customerSessionData = $customerSession->getCustomData();
        $customerEcommToken = $customerSession->getEcommtoken()['ecomm_token'];
        $accountNumber = $customerSessionData['accountNumber'];
        $userId = $customerSessionData['userId'];
        $userName = $customerSessionData['userName'];
        $firstName = $customerSessionData['firstName'];
        $lastName = $customerSessionData['lastName'];
        $email = $customerSessionData['email'];

        $invoicesPay = array();
        if (isset($data['invoice']) && count($data['invoice'])) {
            foreach ($data['invoice']['list'] as $invoice) {
                $invoiceData = array(
                    "invoiceDate" => $invoice['invoiceDate'],
                    "invoiceNumber" => $invoice['invoiceNumber'],
                    "paymentAmount" => str_replace('$', '', $invoice['openAmount'])
                );
                $invoicesPay[] = $invoiceData;
            }
        }
        $payloadData = [
            "token" => $customerEcommToken,
            "branch" => $this->getBranchCode(),
            "accountNumber" => $accountNumber,
            "userId" => $userId,
            "paymentDate" => date('m/d/y'),
            "paymentInfo" => [
                "cashTendered" => $data['cc_amount_approved'],
                "methodOfPayment" => "CreditCard",
                "creditCardType" => $data['cc_type'],
                "creditCardNumber" => $data['cc_number'],
                "creditCardName" => $data['cc_holder'],
                "creditCardApprovalCode" => $data['cc_token']
            ],
            "invoicesToPay" => $invoicesPay
        ];

        $requestData = json_encode($payloadData);
        $logger->info('API Request Payload', (array)$this->jsonHelper->unserialize($requestData));
        try {
            $headers = [
                'Content-Type' => 'application/json'
            ];
            $response = $this->clientCurl($method = 'POST', $serviceUrl, $headers, $requestData);
            if (is_object($response)) {
                $responseBody = $response->getBody();
                $results = json_decode($responseBody, true);
                $logger->info('API Response ', (array)$results);
                if ($response->getStatus() == 200 || $response->getStatus() == 100) {
                    if (!empty($results)) {
                        if (isset($results['data'])) {
                            return $results;
                        } else {
                            return 0;
                        }
                    }
                }
                else{
                    return $results;
                }
            }
        } catch (\Exception $e) {
            $logger->info('Error : ' . $e->getMessage());
            return 'Error : ' . $e->getMessage();
        }
        $logger->info("--------------------------------------------------");
        return 0;
    }
}
