<?php
/**
 * Grouped product type implementation
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DCKAP\Catalog\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use DCKAP\Catalog\Helper\Data as DckapHelper;

/**
 * Grouped product type model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Grouped extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{
    const TYPE_CODE = 'grouped';

    /**
     * Cache key for Associated Products
     *
     * @var string
     */
    protected $_keyAssociatedProducts = '_cache_instance_associated_products';

    /**
     * Cache key for Associated Product Ids
     *
     * @var string
     */
    protected $_keyAssociatedProductIds = '_cache_instance_associated_product_ids';

    /**
     * Cache key for Status Filters
     *
     * @var string
     */
    protected $_keyStatusFilters = '_cache_instance_status_filters';

    /**
     * Product is composite properties
     *
     * @var bool
     */
    protected $_isComposite = true;

    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * Catalog product status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_catalogProductStatus;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product link
     *
     * @var \Magento\GroupedProduct\Model\ResourceModel\Product\Link
     */
    protected $productLinks;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData;
    protected $dckapCatalogHelper;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Msrp\Helper\Data $msrpData
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Framework\App\State $appState,
        \Magento\Msrp\Helper\Data $msrpData,
        DckapHelper $dckapHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->dckapCatalogHelper = $dckapHelper;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $catalogProductLink,
            $storeManager,
            $catalogProductStatus,
            $appState,
            $msrpData
        );
    }
    /**
     * Prepare product and its configuration to be added to some products list.
     *
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $products = [];
        $associatedProductsInfo = [];
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        $productsInfo = $this->getProductInfo($buyRequest, $product, $isStrictProcessMode);
        if (is_string($productsInfo)) {
            return $productsInfo;
        }
        $associatedProducts = !$isStrictProcessMode || !empty($productsInfo)
            ? $this->getAssociatedProducts($product)
            : false;

        foreach ($associatedProducts as $subProduct) {
            $qty = $productsInfo[$subProduct->getId()];
            if (!is_numeric($qty) || empty($qty)) {
                continue;
            }

            $_result = $subProduct->getTypeInstance()->_prepareProduct($buyRequest, $subProduct, $processMode);

            if (is_string($_result)) {
                return $_result;
            } elseif (!isset($_result[0])) {
                return __('Cannot process the item.')->render();
            }
            $buyRequestData = $buyRequest->getData();
            $uom = 'EA';
            $sku = $subProduct->getSku();
            $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
            if ($sessionProductData && isset($sessionProductData[$sku]) && isset($sessionProductData[$sku]['lineItem']['uom']['uomCode'])) {
                $uom = $sessionProductData[$sku]['lineItem']['uom']['uomCode'];
            }
            if ($isStrictProcessMode) {
                $_result[0]->setCartQty($qty);
                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                $_result[0]->addCustomOption(
                    'info_buyRequest',
                    $this->serializer->serialize(
                        [
                            'super_product_config' => [
                                'product_type' => self::TYPE_CODE,
                                'product_id' => $product->getId(),
                            ],
                            'custom_uom' => (isset($buyRequestData['super_group_uom'][$subProduct->getId()])) ? $buyRequestData['super_group_uom'][$subProduct->getId()] : $uom,
                        ]
                    )
                );
                if (isset($buyRequestData['super_group_price'][$subProduct->getId()])) {
                    $_result[0]->setPrice((float)$buyRequestData['super_group_price'][$subProduct->getId()]);
                }
                // add additional options for grouped products begin
                $additionalOptions['custom_uom'] = [
                    'label' => 'UOM',
                    'value' => (isset($buyRequestData['super_group_uom'][$subProduct->getId()])) ? $buyRequestData['super_group_uom'][$subProduct->getId()] : $uom,
                ];
                $_result[0]->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
                // add additional options for grouped products ends
                $products[] = $_result[0];
            } else {
                $associatedProductsInfo[] = [$subProduct->getId() => $qty];
                $product->addCustomOption('associated_product_' . $subProduct->getId(), $qty);
            }
        }

        if (!$isStrictProcessMode || !empty($associatedProductsInfo)) {
            $product->addCustomOption('product_type', self::TYPE_CODE, $product);
            $product->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));

            $products[] = $product;
        }

        if (!empty($products)) {
            return $products;
        }

        return __('Please specify the quantity of product(s).')->render();
    }
}
