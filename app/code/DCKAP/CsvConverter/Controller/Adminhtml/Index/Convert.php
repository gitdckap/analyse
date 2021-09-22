<?php

namespace Dckap\CsvConverter\Controller\Adminhtml\Index;

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
            $headerNew = $attributes = $csvDataNew = $attrValues = $mfrData = [];
            if ($csvData && !empty($csvData)) {
                $header = $csvData[0];
                unset($csvData[0]);
                foreach ($header as $key => $column) {
                    $code = trim(preg_replace('/\s+/', ' ', $column));
                    $code = str_replace('(if Yes Only)', '', $code);
                    $code = str_replace('(', '', str_replace(')', '', str_replace('/', ' ', $code)));
                    $code = rtrim($code);
                    $data['label'] = ucwords(strtolower($code));
                    $code = str_replace(' ', '_', strtolower($code));
                    $data['code'] = str_replace('-', '_', strtolower($code));
                    $attributes[$key] = $data;
                    $headerNew[$key] = str_replace(' ', '_', strtolower($code));
                }
                if ($csvData && !empty($csvData)) {
                    foreach ($csvData as $csvRow) {
                        $csvDataNew[] = array_combine($headerNew, $csvRow);
                    }
                    foreach ($csvDataNew as $csvRow) {
                        $mfr = $csvRow['manufacturer_part_number'];
                        $mfrData[] = $mfr;
                        foreach ($csvRow as $key => $val) {
                            $attrValues[$key][$mfr] = $val;
                        }
                    }
                }
            }

            $attributeCsv1 = $files['attribute_csv1']['tmp_name'];
            if (!isset($attributeCsv1)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            }
            $csvData = $this->csvProcessor->getData($attributeCsv1);
            $attributesNew = $csvDataNew = $attrValuesNew = [];
            if ($csvData && !empty($csvData)) {
                unset($csvData[0]);
                if ($csvData && !empty($csvData)) {
                    foreach ($csvData as $csvRow) {
                        $mfr = $csvRow[0];
                        unset($csvRow[0]);
                        foreach ($csvRow as $key => $val) {
                            if (($key % 2 != 0)) {
                                if ($val != '') {
                                    $code = trim(preg_replace('/\s+/', ' ', $val));
                                    $code = str_replace('(if Yes Only)', '', $code);
                                    $code = str_replace('(', '', str_replace(')', '', str_replace('/', ' ', $code)));
                                    $code = rtrim($code);
                                    $data['label'] = ucwords(strtolower($code));
                                    $code = str_replace('-', '_', $code);
                                    $data['code'] = str_replace(' ', '_', strtolower($code));
                                    $attributesNew[$data['code']] = $data;
                                }
                            } else {
                                if ($val != '') {
                                    $code = trim(preg_replace('/\s+/', ' ', $csvRow[$key - 1]));
                                    $code = str_replace('(if Yes Only)', '', $code);
                                    $code = str_replace('(', '', str_replace(')', '', str_replace('/', ' ', $code)));
                                    $code = rtrim($code);
                                    $code = ucwords(strtolower($code));
                                    $code = str_replace('-', '_', $code);
                                    $code = str_replace(' ', '_', strtolower($code));
                                    $attrValuesNew[$code][$mfr] = $val;
                                }
                            }
                        }
                    }
                }

            }
            $attr = array_merge($attributes, $attributesNew);
            $attrVals = array_merge($attrValues, $attrValuesNew);
            $heading[] = 'sku';
            if ($attr && !empty($attr)) {
                foreach ($attr as $attrVal) {
                    $heading[] = $attrVal['code'];
                }
            }
            $resDataAll = [];
            if ($mfrData && !empty($mfrData)) {
                foreach ($mfrData as $mfrDa) {
                    $resData = [];
                    $resData['sku'] = $mfrDa;
                    if ($heading && !empty($heading)) {
                        foreach ($heading as $headingD) {
                            if ($headingD != 'sku') {
                                if (isset($attrVals[$headingD]) && isset($attrVals[$headingD][$mfrDa])) {
                                    $val = $attrVals[$headingD][$mfrDa];
                                    $resData[] = $val;
                                } else {
                                    $resData[] = '';
                                }
                            }
                        }
                    }
                    $resDataAll[] = $resData;
                }
            }
            if ($postData['type'] == '2') {
                $outputFile = "var/attribute_value_import_" . date('Ymd_His') . ".csv";
                $handle = fopen($outputFile, 'w');
                fputcsv($handle, $heading);
                foreach ($resDataAll as $resDataAl) {
                    fputcsv($handle, $resDataAl);
                }
                $this->downloadCsv($outputFile);
            }


            /* attribute import file */
            $attrImport = [];
            if ($heading && !empty($heading)) {
                foreach ($heading as $headingD) {
                    if ($headingD != 'sku' && $headingD != 'manufacturer_part_number' && $headingD != 'series_model' && $headingD != 'upc_w_check_digit' && $headingD != 'unspsc_code') {
                        if (isset($attrVals[$headingD]) && !empty($attrVals[$headingD])) {
                            foreach ($attrVals[$headingD] as $attrVal) {
                                if ($attrVal != '') {
                                    if (strlen($attrVal) < 30) {
                                        $attrImport[$headingD][] = str_replace(',', '', $attrVal);
                                    } else {
                                        unset($attrImport[$headingD]);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /* get unique values for option */
            $attrImportNew = [];
            if ($attrImport && !empty($attrImport)) {
                foreach ($attrImport as $key => $attrImportData) {
                    $attrImportNew[$key] = array_unique($attrImportData);
                }
            }
//            var_dump($attrImportNew);die;

            $attrHeading = ['attribute_label', 'attribute_code', 'type', 'value', 'visible', 'required', 'searchable', 'filterable', 'comparable', 'visible_on_front'];
            $attrImportNewData = [];
            if ($attr && !empty($attr)) {
                foreach ($attr as $attrV) {
                    $code = $attrV['code'];
                    $data = [];
                    if (isset($attrImportNew[$code])) {
                        $data['attribute_label'] = $attrV['label'];
                        $data['attribute_code'] = $attrV['code'];
                        $data['type'] = 'select';
                        $value = implode(',', $attrImportNew[$attrV['code']]);
                        $data['value'] = $value;
                        $data['visible'] = '1';
                        $data['required'] = '0';
                        $data['searchable'] = '0';
                        $data['filterable'] = '1';
                        $data['comparable'] = '0';
                        $data['visible_on_front'] = '1';
                    } elseif ($code == 'additional_information' || $code == 'long_description' || $code == 'features_benefits' || $code == 'marketing_text') {
                        $data['attribute_label'] = $attrV['label'];
                        $data['attribute_code'] = $attrV['code'];
                        $data['type'] = 'textarea';
                        $data['value'] = '';
                        $data['visible'] = '1';
                        $data['required'] = '0';
                        $data['searchable'] = '0';
                        $data['filterable'] = '0';
                        $data['comparable'] = '0';
                        $data['visible_on_front'] = '1';
                    } else {
                        $data['attribute_label'] = $attrV['label'];
                        $data['attribute_code'] = $attrV['code'];
                        $data['type'] = 'text';
                        $data['value'] = '';
                        $data['visible'] = '1';
                        $data['required'] = '0';
                        $data['searchable'] = '0';
                        $data['filterable'] = '0';
                        $data['comparable'] = '0';
                        $data['visible_on_front'] = '1';
                    }
                    $attrImportNewData[] = $data;
                }
            }
//            var_dump($attrImportNewData);die;

            $outputFile = "var/attribute_import_". date('Ymd_His').".csv";
            $handle = fopen($outputFile, 'w');
            fputcsv($handle, $attrHeading);
            foreach ($attrImportNewData as $resDataAl) {
                fputcsv($handle, $resDataAl);
            }
            $this->downloadCsv($outputFile);

            $this->messageManager->addSuccess(__('File converted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
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
