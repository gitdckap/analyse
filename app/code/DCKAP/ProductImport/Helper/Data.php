<?php

namespace Dckap\ProductImport\Helper;

/**
 * Class Data
 * @package Dckap\ProductImport\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;
    /**
     * @var \Dckap\productImport\Model\Product\Gallery\Video\Processor
     */
    protected $videoGalleryProcessor;

    /**
     * Data constructor.
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Dckap\productImport\Model\Product\Gallery\Video\Processor $videoGalleryProcessor
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\Product $product,
        \Dckap\productImport\Model\Product\Gallery\Video\Processor $videoGalleryProcessor
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_product = $product;
        $this->videoGalleryProcessor = $videoGalleryProcessor;
    }

    /**
     * @param $value1
     * @param $value2
     * @param $value3
     * @param $value4
     * @param bool $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function importProductAttributes($value1, $value2, $value3, $value4, $data = false)
    {
        $eavSetup = $this->eavSetupFactory->create();
        if ($value3 == 'select') {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,
                $value2, [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $value1,
                    'input' => $value3,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => $data['visible'],
                    'required' => $data['required'],
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => $data['searchable'],
                    'filterable' => $data['filterable'],
                    'comparable' => $data['comparable'],
                    'visible_on_front' => $data['visible_on_front'],
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'options' => $value4
                ]);
        } elseif ($value3 == 'yesno') {

            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,
                $value2, [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $value1,
                    'input' => $value3,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => $data['visible'],
                    'required' => $data['required'],
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => $data['searchable'],
                    'filterable' => $data['filterable'],
                    'comparable' => $data['comparable'],
                    'visible_on_front' => $data['visible_on_front'],
                    'used_in_product_listing' => true,
                    'unique' => false
                ]);

        } elseif ($value3 == 'text') {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $value2,
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $value1,
                    'input' => $value3,
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => $data['visible'],
                    'required' => $data['required'],
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => $data['searchable'],
                    'filterable' => $data['filterable'],
                    'comparable' => $data['comparable'],
                    'visible_on_front' => $data['visible_on_front'],
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'wysiwyg_enabled' => true
                ]
            );
        } elseif ($value3 == 'textarea') {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $value2,
                [
                    'type' => 'textarea',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $value1,
                    'input' => $value3,
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => $data['visible'],
                    'required' => $data['required'],
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => $data['searchable'],
                    'filterable' => $data['filterable'],
                    'comparable' => $data['comparable'],
                    'visible_on_front' => $data['visible_on_front'],
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }
    }

    /**
     * @param array $videoData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importProductVideos($videoData = []) {
        if ($videoData && !empty($videoData)) {
            $productId = $this->_product->getIdBySku($videoData ['sku']);
            unset($videoData['sku']);
            $product = $this->_product->load($productId);
            $product->setStoreId(0);

            $videoData ['media_type'] = \Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter::MEDIA_TYPE_CODE;

            if ($product->hasGalleryAttribute()) {
                $this->videoGalleryProcessor->addVideo(
                    $product,
                    $videoData,
                    ['image', 'small_image', 'thumbnail'],
                    false,
                    true
                );
            }
            $product->save();
        }
    }

    /**
     * @param bool $code
     */
    public function removeProductAttribute($code = false)
    {
        if ($code) {
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
        }
    }
}
