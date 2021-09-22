<?php

namespace DCKAP\AkeneoSync\Controller\Adminhtml\ParentProduct;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends Action
{
    protected $scopeConfig;
    protected $resultPageFactory;
    protected $jsonHelper;
    private $_eavConfig;
    protected $_encryptor;
    protected $product;
    protected $date;
    protected $importlog;
    protected $importStep;
    protected $directoryList;
    protected $file;
    protected $readHandler;
    protected $processor;
    protected $gallery;
    protected $eavAttribute;
    protected $mediaConfig;
    protected $fileDriver;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $collection,
        ProductAction $action,
        StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \DCKAP\AkeneoSync\Model\LogFactory $importlog,
        \DCKAP\AkeneoSync\Model\ImportStepFactory $importStep,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Gallery\ReadHandler $readHandler,
        \Magento\Catalog\Model\Product\Gallery\Processor $processor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $gallery,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->productCollection = $collection;
        $this->productAction = $action;
        $this->storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
        $this->_encryptor = $encryptor;
        $this->product = $product;
        $this->date = $date;
        $this->importlog = $importlog;
        $this->importStep = $importStep;
        $this->resourseConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->readHandler = $readHandler;
        $this->processor = $processor;
        $this->gallery = $gallery;
        $this->eavAttribute = $eavAttribute;
        $this->mediaConfig = $mediaConfig;
        $this->fileDriver = $fileDriver;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct($context);
    }


    public function execute()
    {
        $token = $this->getToken();

        if ($token) {
            $mediaEnabled = $this->getConfigValue('akeneo_connector/product/media_enabled');
            $mediaGallery = $this->getConfigValue('akeneo_connector/product/media_gallery');
            $imageAtt = [];
            if ($mediaGallery) {
                $mediaImages = json_decode($mediaGallery, true);
                $imageAtt = array_column($mediaImages, 'attribute');
            }
            $imageMap = $this->getImageCofiguration();
            $ContentType = 'application/json';
            $authValue = 'Bearer ' . $token;

            $headers = [
                'Content-Type' => $ContentType,
                'Authorization' => $authValue,
            ];
            $pageNo = $this->getRequest()->getParam('pageNo');
            $limit = $this->getRequest()->getParam('limit');
            $identifier = $this->getRequest()->getParam('identifier');
            $messages = $this->getRequest()->getParam('messages');
            $logId = $this->getRequest()->getParam('log_id');
            $identifier = ($identifier) ? $identifier : uniqid();


            if ($pageNo == 0) {
                $status = 1;
                $this->addImportStep($identifier, $logId, $messages, $status);
                return $this->jsonResponse(['message' => 'Import success']);
            }

            $mode = $this->getConfigValue('akeneo_connector/products_filters/mode');
            $search_query = "";
            if ($mode == "standard") {
                $search_filter1 = $this->addStandardFilter();
                if ($search_filter1) {
                    $search_query = 'search=' . urlencode('{') . $search_filter1 . urlencode('}');
                }

            } elseif ($mode == "advanced") {
                $msg = "Please choose filter mode value as standard";
                return $this->jsonResponse(['message' => $msg]);
            }

            $url = $this->getConfigValue('akeneo_connector/akeneo_api/base_url');
            $lastChar = substr($url, -1);
            $slash = ($lastChar == "/") ? "" : "/";
            $baseUrl = $this->getConfigValue('akeneo_connector/akeneo_api/base_url') . $slash;
            $serviceUrl = $baseUrl . "api/rest/v1/product-models?" . $search_query . "&page=" . $pageNo . "&with_count=false&pagination_type=page&limit=" . $limit;
            $requestData = [];

            $success_count = 0;
            $response = $this->clientCurl($method = 'GET', $serviceUrl, $headers, $requestData);

            if ($response->getStatus() == 200) {
                $responseBody = $response->getBody();
                $results = json_decode($response->getBody(), true);

                $items = $results['_embedded']['items'];
                if (!empty($items)) {

                    $error_count = 0;
                    $error_message = "";
                    $error_skus = [];
                    $exception_messages = [];
                    foreach ($items as $item) {
                        try {
                            if ($this->product->getIdBySku($item['code'])) {

                                $collection = $this->productCollection->create()->addFieldToFilter('sku', $item['code']);
                                $storeId = $this->storeManager->getStore()->getId();
                                $id = isset($collection->getData()[0]['entity_id']) ? $collection->getData()[0]['entity_id'] : "";
                                $attSetId = isset($collection->getData()[0]['attribute_set_id']) ? $collection->getData()[0]['attribute_set_id'] : "";
                                $ids = array($id);

                                $this->setEmptyValue($item['code'], $attSetId);


                                $attribute_values = $item['values'];
                                $select_value = "";
                                foreach ($attribute_values as $key => $value) {
                                    try {

                                        $update_val = $this->getAttributeValue($key, $value);
                                        if ($this->isProductAttributeExists($key)) {
                                            $this->productAction->updateAttributes($ids, array($key => $update_val), $storeId);
                                        }
                                    } catch (\Exception $e) {
                                        $exception_msg = "[" . $this->date->gmtDate('H:i:s') . "] " . $e->getMessage();
                                        if ($identifier) {
                                            $status = 0;
                                            $this->addImportStep($identifier, $logId, $exception_msg, $status);
                                        }
                                        $exception_message[] = "[" . $this->date->gmtDate('H:i:s') . "] " . $e->getMessage();
                                        $exception_messages = $exception_message;

                                    }
                                }

                                if ($mediaEnabled && !empty($imageAtt)) {
                                    $imageBaseUrl = $baseUrl . "media/cache/thumbnail_small/";
                                    $this->updateProductImage($item['code'], $imageMap, $imageAtt, $token);
                                }
                                $success_count++;

                            } else {
                                $error_sku[] = $item['code'];
                                $error_count++;
                                $error_skus = $error_sku;
                            }
                        } catch (\Exception $e) {
                            $exception_msg = "[" . $this->date->gmtDate('H:i:s') . "] " . $e->getMessage();
                            if ($identifier) {
                                $status = 0;
                                $this->addImportStep($identifier, $logId, $exception_msg, $status);
                            }
                            $exception_message2[] = $exception_msg;
                            $exception_messages = $exception_message2;
                        }
                    }
                    if ($pageNo == 1) {
                        $logId = $this->addLog($identifier, $messages);
                        if ($error_skus) {
                            $error_message = "[" . $this->date->gmtDate('H:i:s') . "] The following SKU(s) does not exist - " . implode(",", $error_skus);
                            if ($identifier) {
                                $status = 0;
                                $this->addImportStep($identifier, $logId, $error_message, $status);
                            }
                        }
                        $success_message = "[" . $this->date->gmtDate('H:i:s') . ']  product(s) Attribute values are imported successfully';
                        $status = 1;
                        $this->addImportStep($identifier, $logId, $success_message, $status);
                    } else {
                        if ($identifier) {
                            $status = 1;
                            $this->addImportStep($identifier, $logId, $messages, $status);
                            if ($error_skus) {
                                $error_message = "[" . $this->date->gmtDate('H:i:s') . "] The following SKU(s) does not exist - " . implode(",", $error_skus);
                                if ($identifier) {
                                    $status = 0;
                                    $this->addImportStep($identifier, $logId, $error_message, $status);
                                }
                            }
                            $status = 1;
                            $success_message = "[" . $this->date->gmtDate('H:i:s') . "]  product(s) Attribute values are imported successfully";
                            $this->addImportStep($identifier, $logId, $success_message, $status);
                        }
                    }

                    return $this->jsonResponse(['complete' => 'no', 'identifier' => $identifier, 'log_id' => $logId, 'error_sku' => $error_skus, 'success' => $success_count, 'error' => $error_count, 'error_message' => $error_message, 'exception_message' => $exception_messages, 'success_message' => $success_message]);
                } else {
                    return $this->jsonResponse(['complete' => 'yes', 'message' => '[' . $this->date->gmtDate('H:i:s') . '] Import Complete']);
                }
            } else {
                return $this->jsonResponse(['message' => 'Import fail']);
            }

        }

    }


    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    public function getToken()
    {

        $clientId = $this->getConfigValue('akeneo_connector/akeneo_api/client_id');
        $secret = $this->getConfigValue('akeneo_connector/akeneo_api/client_secret');
        $username = $this->getConfigValue('akeneo_connector/akeneo_api/username');
        $encrypt_password = $this->getConfigValue('akeneo_connector/akeneo_api/password');
        $password = $this->_encryptor->decrypt($encrypt_password);
        $url = $this->getConfigValue('akeneo_connector/akeneo_api/base_url');
        $lastChar = substr($url, -1);
        $slash = ($lastChar == "/") ? "" : "/";
        $authServiceUrl = $url . $slash . "api/oauth/v1/token";
        $authToken = "";

        if ($clientId && $secret && $username && $password && $authServiceUrl) {
            $encode_value = base64_encode($clientId . ":" . $secret);
            $authReqArray = array("grant_type" => "password", "username" => $username, "password" => $password);
            $authReq = json_encode($authReqArray);

            $authHeaders = [
                'Content-Type' => 'application/json',
                'Authorization' => "Basic " . $encode_value,
            ];

            $tokenResponse = $this->clientCurl($method = 'POST', $authServiceUrl, $authHeaders, $authReq);
            if ($tokenResponse->getStatus() == 200) {
                $tokenResult = json_decode($tokenResponse->getBody(), true);
                $authToken = isset($tokenResult['access_token']) ? $tokenResult['access_token'] : "";
            }
        }
        return $authToken;
    }

    public function clientCurl($method, $serviceUrl, $headers, $requestData = '')
    {
        $response = '';
        try {
            $client = new \Zend_Http_Client();
            $client->setUri($serviceUrl);
            $client->setConfig(array('timeout' => 3000));

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
    }

    public function isProductAttributeExists($field)
    {
        $attr = $this->_eavConfig->getAttribute('catalog_product', $field);
        return ($attr && $attr->getId());
    }

    public function getConfigValue($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    public function addLog($identifier, $messages)
    {
        $model = $this->importlog->create();

        $model->addData([
            "identifier" => $identifier,
            "code" => 'parent_product',
            "name" => 'Parent Product',
            "status" => 1,
            "created_at" => $this->date->gmtDate()
        ]);
        $saveData = $model->save();
        if ($saveData) {
            foreach ($messages as $key => $value) {
                $importModel = $this->importStep->create();
                $importModel->addData([
                    "log_id" => $model->getData('log_id'),
                    "identifier" => $identifier,
                    "number" => '0',
                    "method" => 'Before Import',
                    "message" => $value,
                    "continue" => 1,
                    "status" => 1,
                    "created_at" => $this->date->gmtDate()
                ]);

                $saveImportData = $importModel->save();
            }
            return $importModel->getData('log_id');

        }
    }

    public function addImportStep($identifier, $logId, $message, $status)
    {
        $continue = ($message == "Import complete") ? 0 : 1;

        if (strpos($message, 'error') !== false) {
            $collections = $this->importlog->create()->getCollection()
                ->addFieldToFilter('identifier', array('eq' => $identifier))
                ->addFieldToFilter('log_id', array('eq' => $logId));
            foreach ($collections as $item) {
                $item->setStatus('2');
            }
            $collections->save();
        }

        $importModel = $this->importStep->create();
        $uniqId = $identifier;
        $importModel->addData([
            "log_id" => $logId,
            "identifier" => $uniqId,
            "number" => '1',
            "method" => 'update attribute value',
            "message" => $message,
            "continue" => $continue,
            "status" => $status,
            "created_at" => $this->date->gmtDate()
        ]);
        $saveData = $importModel->save();

    }

    public function addStandardFilter()
    {
        $query = "";
        $updated_mode = $this->getConfigValue('akeneo_connector/products_filters/updated_mode');
        switch ($updated_mode) {
            case ">":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated_greater") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('">"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $value . urlencode('"}]');
                break;
            case "<":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated_lower") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('"<"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $value . urlencode('"}]');
                break;
            case "SINCE LAST N DAYS":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated");
                $dateVal = date("Y-m-d", strtotime("-" . $value . " day")) . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('">"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $dateVal . urlencode('"}]');
                break;
            case "BETWEEN":
                $value1 = $this->getConfigValue("akeneo_connector/products_filters/updated_between_before") . "%2000:00:00";
                $value2 = $this->getConfigValue("akeneo_connector/products_filters/updated_between_after") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('"BETWEEN"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('["') . $value2 . urlencode('"') . ',' . urlencode('"') . $value1 . urlencode('"]}]');
                break;
            default :
                $query = "";
        }

        return $query;
    }


    public function setEmptyValue($sku, $attId)
    {
        $product = $this->productRepository->get($sku);
        $connection = $this->resourseConnection->getConnection();
        $tableName = $connection->getTableName('akeneo_connector_family_attribute_relations');
        $sql = $connection->select()->where('family_entity_id = ?', $attId);
        $sql->from(
            ["tn" => $tableName]
        );
        $result = $connection->fetchAll($sql);
        //$arr = array('materials','sub_brand_name','marketing_text','test_multiselect');
        foreach ($result as $key => $value) {
            $product->setCustomAttribute($value['attribute_code'], null);
        }
        $product->save();
    }

    public function getAttributeValue($key, $value)
    {
        $connection = $this->resourseConnection->getConnection();
        $tableName = $connection->getTableName('akeneo_connector_entities');
        $sql = $connection->select();
        if (is_array($value[0]['data'])) {
            foreach ($value[0]['data'] as $val) {
                $sql->orWhere('code = ?', $key . "_" . $val);
            }
        } else {
            $sql->where('code = ?', $key . "_" . $value[0]['data']);
        }

        $sql->from(
            ["tn" => $tableName]
        );
        $result = $connection->fetchAll($sql);
        $optionValues = array_column($result, 'entity_id');
        $optionVal = implode(',', $optionValues);

        $select_value = isset($optionVal) ? $optionVal : "";
        $update_val = (!empty($select_value)) ? $select_value : $value[0]['data'];
        return $update_val;
    }


    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
    }

    public function getAttributeCode($val, $imageMap)
    {
        $attributeCode = [];
        foreach ($imageMap as $key => $value) {
            if ($value == $val) {
                $eavModel = $this->eavAttribute;
                $attr = $this->eavAttribute->load($key);
                $attributeCode[] = $attr->getAttributeCode();
            }
        }

        return $attributeCode;
    }

    public function getImageCofiguration()
    {
        $ImageMap = [];
        $mediaEnabled = $this->getConfigValue('akeneo_connector/product/media_enabled');
        if ($mediaEnabled) {
            $mappingValue = $this->getConfigValue('akeneo_connector/product/media_images');
            $mappingValues = json_decode($mappingValue, true);
            foreach ($mappingValues as $key => $value) {
                $arrayMap[$value['attribute']] = $value['column'];
                $imageMap = $arrayMap;
            }
        }
        return $imageMap;
    }

    public function updateProductImage($sku, $imageMap, $imageAtt, $token)
    {
        $product = $this->product->loadByAttribute('sku', $sku);
        if ($product) {
            $this->readHandler->execute($product);
            $images = $product->getMediaGalleryImages();
            foreach ($images as $child) {
                $this->gallery->deleteGallery($child->getValueId());
                $this->processor->removeImage($product, $child->getFile());
            }
            $product->save();
        }
        $product = $this->product->loadByAttribute('sku', $sku);

        $imageType = ['image', 'small_image', 'thumbnail'];
        $tmpDir = $this->getMediaDirTmpDir();
        $this->file->checkAndCreateFolder($tmpDir);

        foreach ($imageAtt as $key => $val) {
            if (in_array($val, $imageMap) && $product->getData($val)) {
                $attCode = $this->getAttributeCode($val, $imageMap);
                $filepath = $product->getData($val);
                $imageContent = $this->getImageUrl($token, $filepath);
                $newFileName = $tmpDir . $product->getData($val);
                if ($imageContent) {
                    $this->mediaDirectory->writeFile(
                        $newFileName,
                        $imageContent
                    );
                }
                if ($this->fileDriver->isExists($newFileName)) {
                    $product->setStoreId(0);
                    $product->addImageToMediaGallery($newFileName, $attCode, true, false);
                    $product->save();
                }
            } else if ($product->getData($val)) {
                try {
                    $attCode = array();
                    $filepath = $product->getData($val);
                    $imageContent = $this->getImageUrl($token, $filepath);
                    $newFileName = $tmpDir . $product->getData($val);
                    if ($imageContent) {
                        $this->mediaDirectory->writeFile(
                            $newFileName,
                            $imageContent
                        );
                    }
                    if ($this->fileDriver->isExists($newFileName)) {
                        $product->setStoreId(0);
                        $product->addImageToMediaGallery($newFileName, $attCode, true, false);
                        $product->save();
                    }
                } catch (Exception $e) {
                }
            }
        }

    }

    public function getImageUrl($token, $filepath)
    {

        $ContentType = 'application/json';
        $authValue = 'Bearer ' . $token;

        $headers = [
            'Content-Type' => $ContentType,
            'Authorization' => $authValue,
        ];

        $url = $this->getConfigValue('akeneo_connector/akeneo_api/base_url');
        $lastChar = substr($url, -1);
        $slash = ($lastChar == "/") ? "" : "/";
        $baseUrl = $this->getConfigValue('akeneo_connector/akeneo_api/base_url') . $slash;
        $serviceUrl = $baseUrl . "api/rest/v1/media-files/" . $filepath . "/download";
        $requestData = [];

        $response = $this->clientCurl($method = 'GET', $serviceUrl, $headers, $requestData);

        if ($response->getStatus() == 200) {
            $responseBody = $response->getBody();
            return $responseBody;
        }

        return false;
    }

}