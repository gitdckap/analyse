<?php

namespace Dckap\ProductImport\Controller\Adminhtml\Attributeimport;

use Magento\Framework\HTTP\PhpEnvironment\Request;

/**
 * Class Save
 * @package Dckap\ProductImport\Controller\Adminhtml\Attributeimport
 */
class Save extends \Magento\Backend\App\Action
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
     * @var \Dckap\ProductImport\Helper\Data
     */
    protected $importHelper;
    /**
     * @var Request
     */
    protected $request;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Dckap\ProductImport\Helper\Data $importHelper
     * @param Request $request
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Dckap\ProductImport\Helper\Data $importHelper,
        Request $request
    )
    {
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->csvProcessor = $csvProcessor;
        $this->uploaderFactory = $uploaderFactory;
        $this->importHelper = $importHelper;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $files = $this->request->getFiles()->toArray();
            $tmpfilename = $files['attribute_csv']['tmp_name'];
            //$tmpfilename = $_FILES['attribute_csv']['tmp_name'];
            if (!isset($tmpfilename))
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            $csvData = $this->csvProcessor->getData($tmpfilename);
            foreach ($csvData as $rowIndex => $dataRow) {
                if ($rowIndex > 0) {
                    $values = '';
                    if (!empty($dataRow[3])) {
                        $values = explode('/', $dataRow[3]);
                    }
                    $data = [
                        'visible' => ($dataRow[4] == '1') ? true : false,
                        'required' => ($dataRow[5] == '1') ? true : false,
                        'searchable' => ($dataRow[6] == '1') ? true : false,
                        'filterable' => ($dataRow[7] == '1') ? true : false,
                        'comparable' => ($dataRow[8] == '1') ? true : false,
                        'visible_on_front' => ($dataRow[9] == '1') ? true : false
                    ];
                    $this->importHelper->importProductAttributes($dataRow[0], $dataRow[1], $dataRow[2], $values, $data);
                }
            }
            $this->messageManager->addSuccess(__('Attributes Created successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath('*/attributeimport/index');
    }
}

    
