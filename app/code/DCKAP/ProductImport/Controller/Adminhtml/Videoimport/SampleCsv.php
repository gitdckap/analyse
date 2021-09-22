<?php

namespace Dckap\ProductImport\Controller\Adminhtml\Videoimport;

use Magento\Backend\App\Action;

class SampleCsv extends \Magento\Backend\App\Action
{

    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $heading = ['sku', 'video_id', 'video_title', 'video_description', 'thumbnail', 'video_provider', 'video_metadata', 'video_url'];
        $outputFile = "var/sample_video_import_". date('Ymd_His').".csv";
        $handle = fopen($outputFile, 'w');
        fputcsv($handle, $heading);
        $this->downloadCsv($outputFile);
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