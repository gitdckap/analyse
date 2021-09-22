<?php

namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class ExportExcel
 * @package Dckap\Attachment\Controller\Adminhtml\pdfsection
 */
class ExportExcel extends \Magento\Backend\App\Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * ExportExcel constructor.
     * @param Action\Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $this->_view->loadLayout(false);

        $fileName = 'pdfsection.xml';

        $exportBlock = $this->_view->getLayout()->createBlock('Dckap\Attachment\Block\Adminhtml\Pdfsection\Grid');

        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getExcelFile(),
            DirectoryList::VAR_DIR
        );
    }
}
