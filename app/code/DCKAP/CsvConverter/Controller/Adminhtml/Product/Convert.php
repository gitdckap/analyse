<?php

namespace Dckap\CsvConverter\Controller\Adminhtml\Product;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Convert
 * @package Dckap\CsvConverter\Controller\Adminhtml\Product
 */
class Convert extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * @var
     */
    protected $importHelper;
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $request;

    /**
     * Convert constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request
    ) {
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->csvProcessor = $csvProcessor;
        $this->uploaderFactory = $uploaderFactory;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $files = $this->request->getFiles()->toArray();
            $attributeCsv = $files['attribute_csv']['tmp_name'];
            if (!isset($attributeCsv)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            }
            $csvData = $this->csvProcessor->getData($attributeCsv);
            if ($csvData && !empty($csvData)) {
                $header = $csvData[0];
                unset($csvData[0]);
                $header[0] = 'sku';

                $importProductRawData = [];
                if ($csvData && !empty($csvData)) {
                    foreach ($csvData as $importProductRaw) {
                        $importProductRawData[] = array_combine($header, $importProductRaw);
                    }
                }
                $sampleFile = "pub/media/csv/catalog_product.csv";
                $sampleCsvData = $this->csvProcessor->getData($sampleFile);
                $outputHeader = $sampleCsvData[0];
                $outputHeader1 = array_flip($outputHeader);
                array_walk($outputHeader1, function (&$item) {
                    $item = '';
                });
                $finalData = [];
                $finalData[] = $outputHeader;

                if ($importProductRawData && !empty($importProductRawData)) {
                    foreach ($importProductRawData as $importProductRaw) {
                        $tempData = $outputHeader1;
                        $categoryData = [];
                        $imageData = [];
                        $attachmentData = [];
                        if ($importProductRaw && !empty($importProductRaw)) {
//                            var_dump($importProductRaw);
                            // logic covered in separate function
                            $finalData[] = $this->getFinalData($importProductRaw);
                        }
                    }
                }
                $outputFile = "var/product_import_". date('Ymd_His').".csv";
                $handle = fopen($outputFile, 'w');
                foreach ($finalData as $resDataAl) {
                    fputcsv($handle, $resDataAl);
                }
                $this->downloadCsv($outputFile);
            }

            $this->messageManager->addSuccess(__('File converted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * @param $tempData
     * @return mixed
     */
    protected function getStaticValues($tempData)
    {
        $tempData['attribute_set_code'] = 'Default';
        $tempData['product_type'] = 'simple';
        $tempData['product_websites'] = 'base';
        $tempData['product_online'] = '1';
        $tempData['tax_class_name'] = 'Taxable Goods';
        $tempData['visibility'] = 'Catalog, Search';
        $tempData['display_product_options_in'] = 'Block after Info Column';
        $tempData['gift_message_available'] = 'Use config';
        $tempData['out_of_stock_qty'] = '0';
        $tempData['use_config_min_qty'] = '1';
        $tempData['is_qty_decimal'] = '0';
        $tempData['allow_backorders'] = '0';
        $tempData['use_config_backorders'] = '1';
        $tempData['min_cart_qty'] = '1';
        $tempData['use_config_min_sale_qty'] = '1';
        $tempData['max_cart_qty'] = '10000';
        $tempData['use_config_max_sale_qty'] = '1';
        $tempData['is_in_stock'] = '1';
        $tempData['notify_on_stock_below'] = '1';
        $tempData['use_config_notify_stock_qty'] = '1';
        $tempData['manage_stock'] = '1';
        $tempData['use_config_manage_stock'] = '1';
        $tempData['use_config_qty_increments'] = '1';
        $tempData['qty_increments'] = '1';
        $tempData['use_config_enable_qty_inc'] = '1';
        $tempData['enable_qty_increments'] = '0';
        $tempData['is_decimal_divided'] = '0';
        $tempData['website_id'] = '0';
        return $tempData;
    }

    /**
     * @param $imageData
     * @return array
     */
    protected function getImageData($imageData)
    {
        $images = [];
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if ($imageData && !empty($imageData)) {
            foreach ($imageData as $image) {
                if ($image != '') {
                    $name = basename($image);
                    $ext = pathinfo($image, PATHINFO_EXTENSION);
//                    $name2 =pathinfo($image, PATHINFO_FILENAME);
                    $copyFileFullPath = $mediaDirectory . 'import/' . $name;
                    //while (true) {
                        if (file_exists($copyFileFullPath)) {
                            $filename = str_replace($mediaDirectory.'import/', '', $copyFileFullPath);
                            $newFilename = str_replace('.' . $ext, '_'.date('Ymd_His'). $ext, $filename);
                            $copyFileFullPath = $mediaDirectory . 'import/' . $newFilename;
                        } else {
                            break;
                        }
                    //}
                    $save_name = str_replace($mediaDirectory.'import/', '', $copyFileFullPath);
                    $save_directory = $mediaDirectory.'import/';
                    if (is_writable($save_directory)) {
                        file_put_contents($save_directory . $save_name, file_get_contents($image));
                    }
                    $images[] = $save_name;
                }
            }
        }
        return $images;
    }

    /**
     * @param $imageData
     * @return array
     */
    protected function getImageDataNew($imageData)
    {
        $images = [];
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if ($imageData && !empty($imageData)) {
            foreach ($imageData as $image) {
                if ($image != '') {
                    $name = basename($image);
                    $copyFileFullPath = $mediaDirectory . 'import/' . $name;
                    if (file_exists($copyFileFullPath)) {
                        $images[] = $name;
                    }
                }
            }
        }
        return $images;
    }

    /**
     * @param $categoryData
     * @return string
     */
    protected function getCategoryData($categoryData)
    {
        $resArr[] = 'Default Category';
        if ($categoryData && !empty($categoryData)) {
            foreach ($categoryData as $key => $category) {
                if ($category != '') {
                    $category = str_replace(' & ', ' ', str_replace(' / ', ' ', str_replace(' , ', ' ', $category)));
                    $category = str_replace(' &', ' ', str_replace(' /', ' ', str_replace(' ,', ' ', $category)));
                    $category = str_replace('& ', ' ', str_replace('/ ', ' ', str_replace(', ', ' ', $category)));
                    $category = str_replace('&', ' ', str_replace('/', ' ', str_replace(',', ' ', $category)));
                    $category = trim(preg_replace('/\s+/', ' ', $category));
                    $category = rtrim($category);
                    $resArr[] = $resArr[$key] . '/' . $category;
                }
            }
        }
        unset($resArr[0]);
        return implode(',', $resArr);
    }

    /**
     * @param $file
     */
    public function downloadCsv($file)
    {
        if (file_exists($file)) {
            //set appropriate headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
        }
    }
    protected function getFinalData($importProductRaw)
    {
        foreach ($importProductRaw as $key => $val) {
            if ($key == 'sku') {
                $tempData['sku'] = $val;
                $tempData['url_key'] = str_replace(' ', '-', strtolower($val));
            } elseif (strpos($key, 'PRODUCT NAME') !== false) {
                $tempData['name'] = $val;
            } elseif (strpos($key, 'SHORT DESCRIPTION') !== false) {
                $tempData['short_description'] = $val;
            } elseif (strpos($key, 'CATEGORY DESCRIPTION_Level') !== false) {
                $categoryData[] = $val;
            } elseif (strpos($key, 'INDIVIDUAL ITEM WEIGHT') !== false) {
                if ($val && $val != '') {
                    $tempData['weight'] = str_replace(' Pound', '', $val);
                } else {
                    $tempData['weight'] = '1';
                }
            } elseif (strpos($key, 'INDIVIDUAL ITEM QUANTITY') !== false) {
                if ($val && $val != '') {
                    $tempData['qty'] = str_replace(' Each per Pack', '', $val);
                } else {
                    $tempData['qty'] = '1000';
                }
            } elseif (strpos($key, 'IMAGE LINK') !== false) {
                $imageData[] = $val;
            }
        }
        $tempData['price'] = '99.99';
        $tempData['categories'] = $this->getCategoryData($categoryData);
        $imageDataDetail = $this->getImageData($imageData);
        if ($imageDataDetail && !empty($imageDataDetail)) {
            $tempData['base_image'] = $imageDataDetail[0];
            $tempData['small_image'] = $imageDataDetail[0];
            $tempData['thumbnail_image'] = $imageDataDetail[0];
            unset($imageDataDetail[0]);
            if ($imageDataDetail && !empty($imageDataDetail)) {
                $tempData['additional_images'] = implode(',', $imageDataDetail);
            }
        }
        $tempDataNew = $this->getStaticValues($tempData);
        return $tempDataNew;
    }
}
