<?php

namespace Dckap\Attachment\Block\Index;

/**
 * Class Product
 * @package Dckap\Attachment\Block\Index
 */
class Product extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Dckap\Attachment\Model\PdfattachmentFactory
     */
    protected $pdfattachmentFactory;
    /**
     * @var \Dckap\Attachment\Model\PdfsectionFactory
     */
    protected $pdfsectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Product constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Dckap\Attachment\Model\PdfattachmentFactory $pdfattachmentFactory
     * @param \Dckap\Attachment\Model\PdfsectionFactory $pdfsectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Dckap\Attachment\Model\PdfattachmentFactory $pdfattachmentFactory,
        \Dckap\Attachment\Model\PdfsectionFactory $pdfsectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->pdfattachmentFactory = $pdfattachmentFactory;
        $this->pdfsectionFactory = $pdfsectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
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
    public function getAttachments()
    {
        $attachments = [];
        $product = $this->registry->registry('current_product');
        $sku = $product->getData('sku');
        $collections = $this->pdfattachmentFactory->create()->getCollection()
            ->addFieldToFilter('sku', ['eq' => $sku]);
        if ($collections && !empty($collections)) {
            foreach ($collections as $item) {
                $attachments[$item->getData('section_id')][] = $item->getData();
//                $attachments[] = $item->getData();
            }
        }
        return $attachments;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}
