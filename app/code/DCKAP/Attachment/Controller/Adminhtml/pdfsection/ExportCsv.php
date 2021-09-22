<?php

namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class ExportCsv
 * @package Dckap\Attachment\Controller\Adminhtml\pdfsection
 */
class ExportCsv extends \Magento\Backend\App\Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * ExportCsv constructor.
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

        $fileName = 'pdfsection.csv';

        $exportBlock = $this->_view->getLayout()->createBlock('Dckap\Attachment\Block\Adminhtml\Pdfsection\Grid');

        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
