<?php

namespace DCKAP\Extension\Job;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Output as OutputHelper;
use Akeneo\Connector\Helper\ProductFilters;
use Akeneo\Connector\Helper\Serializer as JsonSerializer;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Akeneo\Connector\Job\Option as JobOption;
use Akeneo\Connector\Model\Source\Attribute\Metrics as AttributeMetrics;
use Magento\Catalog\Model\Product as BaseProductModel;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Catalog\Model\ProductRepository as productRepository;
use \DCKAP\Catalog\Helper\View as dckapHelper;
use \DCKAP\Extension\Helper\Data as extensionHelper;
use Zend_Db_Expr as Expr;
use Magento\Eav\Api\Data\AttributeInterface;
use Zend_Db_Statement_Exception;
use DCKAP\Extension\Model\Payment as PaymentModel;

/**
 * Product Attributes setting
 *
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2019 Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */
class Product extends \Akeneo\Connector\Job\Product
{
    /**
     * @var dckapHelper
     */
    protected $dckapHelper;
    /**
     * @var extensionHelper
     */
    protected $extensionHelper;
    /**
     * @var productRepository
     */
    protected $productRepository;
    protected $paymentModel;
    /**
     * Product constructor.
     * @param OutputHelper $outputHelper
     * @param ManagerInterface $eventManager
     * @param Authenticator $authenticator
     * @param ProductImportHelper $entitiesHelper
     * @param ConfigHelper $configHelper
     * @param EavConfig $eavConfig
     * @param ProductFilters $productFilters
     * @param ScopeConfigInterface $scopeConfig
     * @param JsonSerializer $serializer
     * @param BaseProductModel $product
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param TypeListInterface $cacheTypeList
     * @param StoreHelper $storeHelper
     * @param JobOption $jobOption
     * @param AttributeMetrics $attributeMetrics
     * @param StoreManagerInterface $storeManager
     * @param dckapHelper $dckapHelper
     * @param productRepository $productRepository
     * @param extensionHelper $extensionHelper
     * @param array $data
     */
    public function __construct(
        OutputHelper $outputHelper,
        ManagerInterface $eventManager,
        Authenticator $authenticator,
        ProductImportHelper $entitiesHelper,
        ConfigHelper $configHelper,
        EavConfig $eavConfig,
        ProductFilters $productFilters,
        ScopeConfigInterface $scopeConfig,
        JsonSerializer $serializer,
        BaseProductModel $product,
        ProductUrlPathGenerator $productUrlPathGenerator,
        TypeListInterface $cacheTypeList,
        StoreHelper $storeHelper,
        JobOption $jobOption,
        AttributeMetrics $attributeMetrics,
        StoreManagerInterface $storeManager,
        dckapHelper $dckapHelper,
        productRepository $productRepository,
        extensionHelper $extensionHelper,
        PaymentModel $paymentModel,
        array $data = []
    ) {
        parent::__construct($outputHelper, $eventManager, $authenticator, $entitiesHelper, $configHelper, $eavConfig,
            $productFilters, $scopeConfig, $serializer, $product, $productUrlPathGenerator, $cacheTypeList,
            $storeHelper, $jobOption, $attributeMetrics, $storeManager, $data);
        $this->dckapHelper = $dckapHelper;
        $this->productRepository = $productRepository;
        $this->extensionHelper = $extensionHelper;
        $this->paymentModel = $paymentModel;
    }

    /**
     * set keywords to product
     *
     * @return void
     * @throws LocalizedException
     */
    public function setKeywords()
    {
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());

//        $sql = $connection->select()->from($tmpTable);
//        $result = $connection->fetchAll($sql);
        $result = $this->paymentModel->getTableData($tmpTable);
        foreach ($result as $res) {
            $id = $res['_entity_id'];
            $product = $this->productRepository->getById($id);
            $keywords = [];
            $attributeGroups = $this->dckapHelper->getAttributeGroups($product->getAttributeSetId());
            if ($attributeGroups && !empty($attributeGroups)) {
                foreach ($attributeGroups as $attributeGroup) {
                    if ($attributeGroup['attribute_group_code'] == 'other') {
                        $attrs = $this->dckapHelper->getGroupAttributes($product, $attributeGroup['attribute_group_id']
                            , $product->getAttributes());
                        foreach ($attrs as $_data) {
                            if (strpos($_data->getAttributeCode(), 'keyword') !== false) {
                                $keywords[] = $_data->getFrontend()->getValue($product);
                            }
                        }
                    }
                }
            }
            if ($keywords && !empty($keywords)) {
                $metaKeyword = '';
                $metaKeyword = implode(',', $keywords);
                $_product = $this->productRepository->getById($id);
                $price = $_product->getPrice();
                $_product->setData('meta_keyword', $metaKeyword);
                /* Set product price is 0.00 if price is empty */
                if ($price == null) {
                    $_product->setData('price', 0.00);
                }
                $this->productSave($_product);
                //$this->productRepository->save($_product);
            }
        }
    }

    /**
     * Set values to attributes
     *
     * @return void
     * @throws LocalizedException
     */
    public function setValues()
    {
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());
        /** @var string[] $attributeScopeMapping */
        $attributeScopeMapping = $this->entitiesHelper->getAttributeScopeMapping();
        /** @var array $stores */
        $stores = $this->storeHelper->getAllStores();
        /** @var string[] $columns */
        $columns = array_keys($connection->describeTable($tmpTable));

        // Format url_key columns
        /** @var string|array $matches */
        $matches = $this->configHelper->getAttributeMapping();
        if (is_array($matches)) {
            /** @var array $stores */
            $stores = $this->storeHelper->getAllStores();

            /** @var array $match */
            foreach ($matches as $match) {
                if (!isset($match['akeneo_attribute'], $match['magento_attribute'])) {
                    continue;
                }
                /** @var string $magentoAttribute */
                $magentoAttribute = $match['magento_attribute'];

                /**
                 * @var string $local
                 * @var string $affected
                 */
                foreach ($stores as $local => $affected) {
                    if ($magentoAttribute === 'url_key') {
                        $this->entitiesHelper->formatUrlKeyColumn($tmpTable, $local);
                    }
                }
            }
            $this->entitiesHelper->formatUrlKeyColumn($tmpTable);
        }

        /** @var string $adminBaseCurrency */
        $adminBaseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
        /** @var mixed[] $values */
        $values = [
            0 => [
                'options_container' => '_options_container',
                'tax_class_id'      => '_tax_class_id',
                'visibility'        => '_visibility',
            ],
        ];

        if ($connection->tableColumnExists($tmpTable, 'enabled')) {
            $values[0]['status'] = '_status';
        }

        /** @var mixed[] $taxClasses */
        $taxClasses = $this->configHelper->getProductTaxClasses();
        if (count($taxClasses)) {
            foreach ($taxClasses as $storeId => $taxClassId) {
                $values[$storeId]['tax_class_id'] = new Expr($taxClassId);
            }
        }

        /** @var string $column */
        foreach ($columns as $column) {
            /** @var string[] $columnParts */
            $columnParts = explode('-', $column, 2);
            /** @var string $columnPrefix */
            $columnPrefix = $columnParts[0];

            if (in_array($columnPrefix, $this->excludedColumns) || preg_match('/-unit/', $column)) {
                continue;
            }

            if (!isset($attributeScopeMapping[$columnPrefix])) {
                // If no scope is found, attribute does not exist
                continue;
            }

            if (empty($columnParts[1])) {
                // No channel and no locale found: attribute scope naturally is Global
                $values[0][$columnPrefix] = $column;

                continue;
            }

            /** @var int $scope */
            $scope = (int)$attributeScopeMapping[$columnPrefix];
            if ($scope === ScopedAttributeInterface::SCOPE_GLOBAL &&
                !empty($columnParts[1]) && $columnParts[1] === $adminBaseCurrency) {
                // This attribute has global scope with a suffix: it is a price with its currency
                // If Price scope is set to Website, it will be processed afterwards as any website scoped attribute
                $values[0][$columnPrefix] = $column;

                continue;
            }

            /** @var string $columnSuffix */
            $columnSuffix = $columnParts[1];
            if (!isset($stores[$columnSuffix])) {
                // No corresponding store found for this suffix
                continue;
            }

            /** @var mixed[] $affectedStores */
            $affectedStores = $stores[$columnSuffix];
            /** @var mixed[] $store */
            foreach ($affectedStores as $store) {
                // Handle website scope
                if ($scope === ScopedAttributeInterface::SCOPE_WEBSITE &&
                    !$store['is_website_default'])
                {
                    continue;
                }

                if ($scope === ScopedAttributeInterface::SCOPE_STORE ||
                    empty($store['siblings']))
                {
                    $values[$store['store_id']][$columnPrefix] = $column;

                    continue;
                }

                /** @var string[] $siblings */
                $siblings = $store['siblings'];
                /** @var string $storeId */
                foreach ($siblings as $storeId) {
                    $values[$storeId][$columnPrefix] = $column;
                }
            }
        }

        /** @var int $entityTypeId */
        $entityTypeId = $this->configHelper->getEntityTypeId(BaseProductModel::ENTITY);

        /**
         * @var string   $storeId
         * @var string[] $data
         */
        foreach ($values as $storeId => $data) {
            /*Preserve Visibility and status configuration logic */
            if ($this->extensionHelper->getIsAkeneoVisbilitySync()) {
                    unset($data['visibility']);
            }
            if ($this->extensionHelper->getIsAkeneoStatusSync()) {
                unset($data['status']);
            }

            $this->entitiesHelper->setValues(
                $this->getCode(),
                'catalog_product_entity',
                $data,
                $entityTypeId,
                $storeId,
                AdapterInterface::INSERT_ON_DUPLICATE
            );

        }
    }

    /**
     * Link configurable with children
     *
     * @return void
     * @throws Zend_Db_Statement_Exception
     * @throws LocalizedException
     */
    public function linkConfigurable()
    {
        if ($this->extensionHelper->getIsAkeneoVisbilitySync() || $this->extensionHelper->getIsAkeneoStatusSync()) {
            $this->setVisibilityAndStatus();
        }

       /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());

        /** @var string|null $groupColumn */
        $groupColumn = null;
        if ($connection->tableColumnExists($tmpTable, 'parent')) {
            $groupColumn = 'parent';
        }
        if ($connection->tableColumnExists($tmpTable, 'groups') && !$groupColumn) {
            $groupColumn = 'groups';
        }
        if (!$groupColumn) {
            $this->setStatus(false);
            $this->setMessage(__('Columns groups or parent not found'));

            return;
        }

        /** @var Select $configurableSelect */
//        $configurableSelect = $connection->select()->from($tmpTable, ['_entity_id', '_axis', '_children'])
//            ->where('_type_id = ?', 'configurable')->where('_axis IS NOT NULL')->where('_children IS NOT NULL');
        $query = $this->paymentModel->getConfigurableSelect($tmpTable);

        /** @var int $stepSize */
        $stepSize = self::CONFIGURABLE_INSERTION_MAX_SIZE;
        /** @var array $valuesLabels */
        $valuesLabels = [];
        /** @var array $valuesRelations */
        $valuesRelations = []; // catalog_product_relation
        /** @var array $valuesSuperLink */
        $valuesSuperLink = []; // catalog_product_super_link
        /** @var Zend_Db_Statement_Pdo $query */
       // $query = $connection->query($configurableSelect);
        /** @var array $stores */
        $stores = $this->storeHelper->getStores('store_id');

        /** @var array $row */
        while ($row = $query->fetch()) {
            if (!isset($row['_axis'])) {
                continue;
            }

            /** @var array $attributes */
            $attributes = explode(',', $row['_axis']);
            /** @var int $position */
            $position = 0;

            /** @var int $id */
            foreach ($attributes as $id) {
                if (!is_numeric($id) || !isset($row['_entity_id']) || !isset($row['_children'])) {
                    continue;
                }

                /** @var bool $hasOptions */
//                $hasOptions = (bool)$connection->fetchOne(
//                    $connection->select()->from($this->entitiesHelper->getTable('eav_attribute_option'),
//                        [new Expr(1)])->where('attribute_id = ?', $id)->limit(1)
//                );
                $hasOptions = $this->paymentModel->checkOptions($id);

                if (!$hasOptions) {
                    continue;
                }

                /** @var array $values */
                $values = [
                    'product_id'   => $row['_entity_id'],
                    'attribute_id' => $id,
                    'position'     => $position++,
                ];
                $connection->insertOnDuplicate(
                    $this->entitiesHelper->getTable('catalog_product_super_attribute'),
                    $values,
                    []
                );

                /** @var string $superAttributeId */
//                $superAttributeId = $connection->fetchOne(
//                    $connection->select()->from($this->entitiesHelper->getTable('catalog_product_super_attribute'))
//                        ->where('attribute_id = ?', $id)->where('product_id = ?', $row['_entity_id'])->limit(1)
//                );
                $superAttributeId = $this->paymentModel->getSuperAttributeId($id, $row['_entity_id']);

                /**
                 * @var int   $storeId
                 * @var array $affected
                 */
                foreach ($stores as $storeId => $affected) {
                    $valuesLabels[] = [
                        'product_super_attribute_id' => $superAttributeId,
                        'store_id'                   => $storeId,
                        'use_default'                => 0,
                        'value'                      => '',
                    ];
                }

                /** @var array $children */
                $children = explode(',', $row['_children']);
                /** @var string $child */
                foreach ($children as $child) {
                    /** @var int $childId */
//                    $childId = (int)$connection->fetchOne(
//                        $connection->select()->from($this->entitiesHelper->getTable('catalog_product_entity'), ['entity_id'])
//                            ->where('sku = ?', $child)->limit(1)
//                    );
                    $childId = $this->paymentModel->getChildId($child);

                    if (!$childId) {
                        continue;
                    }

                    $valuesRelations[] = [
                        'parent_id' => $row['_entity_id'],
                        'child_id'  => $childId,
                    ];

                    $valuesSuperLink[] = [
                        'product_id' => $childId,
                        'parent_id'  => $row['_entity_id'],
                    ];
                }

                if (count($valuesSuperLink) > $stepSize) {
                    $connection->insertOnDuplicate(
                        $this->entitiesHelper->getTable('catalog_product_super_attribute_label'),
                        $valuesLabels,
                        []
                    );

                    $connection->insertOnDuplicate(
                        $this->entitiesHelper->getTable('catalog_product_relation'),
                        $valuesRelations,
                        []
                    );

                    $connection->insertOnDuplicate(
                        $this->entitiesHelper->getTable('catalog_product_super_link'),
                        $valuesSuperLink,
                        []
                    );

                    $valuesLabels    = [];
                    $valuesRelations = [];
                    $valuesSuperLink = [];
                }
            }
        }

        if (count($valuesSuperLink) > 0) {
            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('catalog_product_super_attribute_label'),
                $valuesLabels,
                []
            );

            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('catalog_product_relation'),
                $valuesRelations,
                []
            );

            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('catalog_product_super_link'),
                $valuesSuperLink,
                []
            );
        }
    }

    /**
     * set visibility and status for new product from akeneo
     *
     * @return void
     * @throws LocalizedException
     */
    public function setVisibilityAndStatus()
    {
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());
        $entityTypeId = $this->configHelper->getEntityTypeId(BaseProductModel::ENTITY);
//        $sql = $connection->select()->from($tmpTable);
//        $result = $connection->fetchAll($sql);
        $result = $this->paymentModel->getTableData($tmpTable);
        foreach ($result as $res) {
            $product = $this->productRepository->getById($res['_entity_id']);
            if ($this->extensionHelper->getIsAkeneoVisbilitySync() && $res['_is_new']) {
                $attribute = $this->entitiesHelper->getAttribute('visibility', $entityTypeId);
                $values = [
                    'attribute_id' => new Expr($attribute[AttributeInterface::ATTRIBUTE_ID]),
                    'store_id' => 0,
                    'entity_id' => $product->getId(),
                    'value' => $res['_visibility']
                ];
                $connection->insertOnDuplicate('catalog_product_entity_int', $values);
            }

            if ($this->extensionHelper->getIsAkeneoStatusSync() && $res['_is_new']) {
                $attribute = $this->entitiesHelper->getAttribute('status', $entityTypeId);
                $values = [
                    'attribute_id' => new Expr($attribute[AttributeInterface::ATTRIBUTE_ID]),
                    'store_id' => 0,
                    'entity_id' => $product->getId(),
                    'value' => $res['_status']
                ];
                $connection->insertOnDuplicate('catalog_product_entity_int', $values);
            }

        }
    }

    /**
     * @param $_product
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function productSave($_product)
    {
        $this->productRepository->save($_product);
    }

}

