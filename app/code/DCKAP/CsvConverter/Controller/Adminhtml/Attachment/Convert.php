<?php

namespace Dckap\CsvConverter\Controller\Adminhtml\Attachment;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Convert
 * @package Dckap\CsvConverter\Controller\Adminhtml\Attachment
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
                $outputHeader = $header;
                $outputHeader1 = array_flip($outputHeader);
                array_walk($outputHeader1, function (&$item) {
                    $item = '';
                });
                $finalData = [];
                $finalData[] = $header;
                if ($importProductRawData && !empty($importProductRawData)) {
                    foreach ($importProductRawData as $importProductRaw) {
                        $tempData = $outputHeader1;
                        $attachmentData = [];
                        if ($importProductRaw && !empty($importProductRaw)) {
                            foreach ($importProductRaw as $key => $val) {
                                if ($key == 'sku') {
                                    $tempData['sku'] = $val;
                                } elseif ((strpos($key, 'MANUFACTURER') !== false) && (strpos($key, 'DOCUMENT') !== false)) {
                                    $attachmentData[$key] = $val;
                                }
                            }
                            $attachmentDataDetail = $this->getAttachmentData($attachmentData);
                            if ($attachmentDataDetail && !empty($attachmentDataDetail)) {
                                foreach ($attachmentDataDetail as $key => $attachmentDataD) {
                                    $tempData[$key] = $attachmentDataD;
                                }
                            }
                            $finalData[] = $tempData;
                        }
                    }
                }
                $outputFile = "var/attachment_import_". date('Ymd_His').".csv";
                $handle = fopen($outputFile, 'w');
                foreach ($finalData as $resDataAl) {
                    fputcsv($handle, $resDataAl);
                }
                $this->downloadCsv($outputFile);
            }

            $this->messageManager->addSuccess(__('Attachment File created successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * @param $attachmentData
     * @return array
     */
    protected function getAttachmentData($attachmentData)
    {
        $attachments = [];
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if ($attachmentData && !empty($attachmentData)) {
            foreach ($attachmentData as $key => $attachment) {
                if ($attachment != '') {
                    $name = basename($attachment);
                    $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                    $copyFileFullPath = $mediaDirectory . 'import/attachment/' . $name;
                    //while (true) {
                        if (file_exists($copyFileFullPath)) {
                            $filename = str_replace($mediaDirectory.'import/attachment/', '', $copyFileFullPath);
                            $newFilename = str_replace('.' . $ext, '_'.date('Ymd_His'). $ext, $filename);
                            $copyFileFullPath = $mediaDirectory . 'import/attachment/' . $newFilename;
                        } else {
                            break;
                        }
                    //}
                    $save_name = str_replace($mediaDirectory.'import/attachment/', '', $copyFileFullPath);
                    $save_directory = $mediaDirectory.'import/attachment/';
                    if (is_writable($save_directory)) {
                        file_put_contents($save_directory . $save_name, file_get_contents($attachment));
                    }
                    $attachments[$key] = $save_name;
                }
            }
        }
        return $attachments;
    }

    /**
     * @param $attachmentData
     * @return array
     */
    protected function getAttachmentDataNew($attachmentData)
    {
        $attachments = [];
        $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if ($attachmentData && !empty($attachmentData)) {
            foreach ($attachmentData as $key => $attachment) {
                if ($attachment != '') {
                    $name = basename($attachment);
                    $copyFileFullPath = $mediaDirectory . 'import/attachment/' . $name;
                    if (file_exists($copyFileFullPath)) {
                        $attachments[$key] = $name;
                    }
                }
            }
        }
        return $attachments;
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
}
