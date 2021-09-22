<?php

namespace Dckap\CsvConverter\Controller\Adminhtml\Video;

use Magento\Framework\App\Filesystem\DirectoryList;

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
    protected $uploaderFactory;
    protected $importHelper;
    protected $request;

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
                $outputHeader = ['sku', 'video_id', 'video_title', 'video_description', 'thumbnail', 'video_provider', 'video_metadata', 'video_url'];
                $outputHeader1 = array_flip($outputHeader);
                array_walk($outputHeader1, function (&$item) {
                    $item = '';
                });
                $finalData = [];
                $finalData[] = $outputHeader;
                if ($importProductRawData && !empty($importProductRawData)) {
                    foreach ($importProductRawData as $importProductRaw) {
                        if ($importProductRaw['MANUFACTURER VIDEO URL'] != '') {
                            $tempData = $outputHeader1;
                            $tempData['sku'] = $importProductRaw['sku'];
                            $tempData['video_url'] = $importProductRaw['MANUFACTURER VIDEO URL'];
                            $tempData['video_id'] = $tempData['sku'] . '-video';
                            $videoProvider = $this->getVideoType($importProductRaw['MANUFACTURER VIDEO URL']);
                            $tempData['video_provider'] = $videoProvider;
                            $tempData['thumbnail'] = 'sample.jpg';
                            if ($videoProvider != '') {
                                $finalData[] = $tempData;
                            }
                        }
                    }
                }
                $outputFile = "var/video_import_". date('Ymd_His').".csv";
                $handle = fopen($outputFile, 'w');
                foreach ($finalData as $resDataAl) {
                    fputcsv($handle, $resDataAl);
                }
                $this->downloadCsv($outputFile);
            }

            $this->messageManager->addSuccess(__('Video Import File created successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
    protected function getVideoType($url)
    {
        if ((strpos($url, 'youtube') > 0) || (strpos($url, 'youtu.be') > 0)) {
            return 'youtube';
        } elseif (strpos($url, 'vimeo') > 0) {
            return 'vimeo';
        } else {
            return '';
        }
    }

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
}
