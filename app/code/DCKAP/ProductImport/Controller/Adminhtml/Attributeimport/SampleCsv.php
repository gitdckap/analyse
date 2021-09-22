<?php

namespace Dckap\ProductImport\Controller\Adminhtml\Attributeimport;

use Magento\Backend\App\Action;

/**
 * Class SampleCsv
 * @package Dckap\ProductImport\Controller\Adminhtml\Attributeimport
 */
class SampleCsv extends \Magento\Backend\App\Action
{

    /**
     * SampleCsv constructor.
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $outputFile = "pub/media/sample-attribute-import.csv";
        $this->downloadCsv($outputFile);
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