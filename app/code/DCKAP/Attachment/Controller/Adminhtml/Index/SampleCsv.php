<?php
namespace Dckap\Attachment\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class SampleCsv extends \Magento\Backend\App\Action
{
    protected $attachmentHelper;

    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Helper\Data $attachmentHelper,
        \Magento\Framework\Filesystem\Driver\File $fileSystem
    ) {
        parent::__construct($context);
        $this->attachmentHelper = $attachmentHelper;
        $this->_fileSystem = $fileSystem;
    }

    public function execute()
    {
        $sections = $this->attachmentHelper->getPdfSections();
        $heading = ['sku'];
        if ($sections && !empty($sections)) {
            foreach ($sections as $section) {
                $heading = $section['section_name'];
            }
        }
        $outputFile = "var/sample_pdf_attachment_". date('Ymd_His').".csv";
        $handle = $this->_fileSystem->fileOpen($outputFile, 'w');
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
            $this->_fileSystem->fileRead($file);
        }
    }
}
