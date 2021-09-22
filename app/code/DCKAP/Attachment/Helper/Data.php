<?php

namespace Dckap\Attachment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Dckap\Attachment\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Dckap\Attachment\Model\PdfsectionFactory
     */
    protected $pdfsectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Dckap\Attachment\Model\PdfsectionFactory $pdfsectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Dckap\Attachment\Model\PdfsectionFactory $pdfsectionFactory
    ) {
        $this->pdfsectionFactory = $pdfsectionFactory;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getPdfSections()
    {
        $sections = [];
        $collections = $this->pdfsectionFactory->create()->getCollection();
        if ($collections && !empty($collections)) {
            foreach ($collections as $item) {
                $sections[] = $item->getData();
            }
        }
        return $sections;
    }

    /**
     * @return array
     */
    public function getSectionOptionArray()
    {
        $sections = [];
        $collections = $this->pdfsectionFactory->create()->getCollection();
        if ($collections && !empty($collections)) {
            foreach ($collections as $item) {
                $sections[$item->getId()] = $item->getSectionName();
            }
        }
        return $sections;
    }
}
