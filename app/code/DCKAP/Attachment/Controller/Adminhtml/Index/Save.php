<?php

namespace Dckap\Attachment\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Save
 * @package Dckap\Attachment\Controller\Adminhtml\Index
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemIo;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    /**
     * @var \Dckap\Attachment\Model\PdfattachmentFactory
     */
    protected $pdfattachmentFactory;
    /**
     * @var \Dckap\Attachment\Helper\Data
     */
    protected $attachmentHelper;
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $request;


    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Dckap\Attachment\Model\PdfattachmentFactory $pdfattachmentFactory
     * @param \Dckap\Attachment\Helper\Data $attachmentHelper
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $request
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\Io\File $filesystemIo,
        \Magento\Framework\Filesystem $filesystem,
        \Dckap\Attachment\Model\PdfattachmentFactory $pdfattachmentFactory,
        \Dckap\Attachment\Helper\Data $attachmentHelper,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request
    ) {
        parent::__construct($context);
        $this->csvProcessor = $csvProcessor;
        $this->filesystemIo = $filesystemIo;
        $this->filesystem = $filesystem;
        $this->pdfattachmentFactory = $pdfattachmentFactory;
        $this->attachmentHelper = $attachmentHelper;
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        $files = $this->request->getFiles()->toArray();

        if (!isset($files['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $importProductRawData = $this->csvProcessor->getData($files['tmp_name']);

        $header = $importProductRawData[0];
        unset($importProductRawData[0]);

        $newHeader = ['sku'];
        $sections = array_flip($this->attachmentHelper->getSectionOptionArray());
//        var_dump($sections);
        if ($header && !empty($header)) {
            foreach ($header as $key => $headerCol) {
//                var_dump($headerCol);
                if ($headerCol != 'sku') {
                    $newHeader[$key] = $sections[$headerCol];
                }
            }
        }
        $importProductRawDataNew = [];
        if ($importProductRawData && !empty($importProductRawData)) {
            foreach ($importProductRawData as $importProductRaw) {
                $importProductRawDataNew[] = array_combine($newHeader, $importProductRaw);
            }
        }

        try {
            $rootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            if ($importProductRawDataNew && !empty($importProductRawDataNew)) {
                foreach ($importProductRawDataNew as $rowIndex => $dataRow) {
                    $sku = $dataRow['sku'];
                    unset($dataRow['sku']);
                    if ($dataRow && !empty($dataRow)) {
                        foreach ($dataRow  as $key => $filename) {
                            if ($filename != '') {
                                $filePath = $rootDirectory . $postData['import_attachment_dir'] . '/' . $filename;
                                $copyFileFullPath = $mediaDirectory . 'attachment/' . $filename;
                                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                                //while (true) {
                                    if (file_exists($copyFileFullPath)) {
                                        $filename = str_replace($mediaDirectory.'attachment/', '', $copyFileFullPath);
                                        $newFilename = str_replace('.' . $ext, '_1.' . $ext, $filename);
                                        $copyFileFullPath = $mediaDirectory . 'attachment/' . $newFilename;
                                    } else {
                                        break;
                                    }
                                //}
                                $fileStatus = $this->filesystemIo->cp($filePath, $copyFileFullPath);
                                $data = [];
                                $data['sku'] = $sku;
                                $data['section_id'] = $key;
                                $data['attachment'] = str_replace($mediaDirectory, '', $copyFileFullPath);
                                $data['file_type'] = $ext;
                                /**
                                 * Add condition to check particular 'sku' already has pdf attachment or not
                                 *
                                 * Delete row if that sku already exist
                                 */
                                if ($postData['type'] == '2') {
                                    $collections = $this->pdfattachmentFactory->create()->getCollection()
                                        ->addFieldToFilter('sku', ['eq' => $data['sku']])
                                        ->addFieldToFilter('section_id', ['eq' => $data['section_id']]);
                                    foreach ($collections as $item) {
                                        $item->delete();
                                    }
                                }
                                $this->setDataEach($data);
                            }
                        }
                    }
                }
            }
            
            $this->messageManager->addSuccess(__('The Pdfattachment has been imported successfully.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Pdfattachment.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $data
     * @throws \Exception
     */
    public function setDataEach($data){
        $model = $this->pdfattachmentFactory->create();
        $model->setData($data);
        $model->save();
    }
}
