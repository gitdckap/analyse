<?php

namespace DCKAP\Extension\Job;

use Akeneo\Pim\ApiClient\Pagination\PageInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Eav\Model\Config;
use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Attribute as AttributeHelper;
use Akeneo\Connector\Helper\Import\Entities as EntitiesHelper;
use Akeneo\Connector\Helper\Output as OutputHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use \Zend_Db_Expr as Expr;
use Zend_Db_Statement_Interface;

/**
 * Attribute Setup
 *
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.pimgento.com/
 */
class Attribute extends \Akeneo\Connector\Job\Attribute
{

    /**
     * Add attributes if not exists
     *
     * @return void
     */
    public function addAttributes()
    {
        /** @var array $columns */
        $columns = $this->attributeHelper->getSpecificColumns();
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->getCode());

        /** @var string $adminLang */
        $adminLang = $this->storeHelper->getAdminLang();
        /** @var string $adminLabelColumn */
        $adminLabelColumn = sprintf('labels-%s', $adminLang);

        /** @var Select $import */
        $import = $connection->select()->from($tmpTable);
        /** @var Zend_Db_Statement_Interface $query */
        $query = $connection->query($import);
        /** @var string[] $mapping */
        $mapping = $this->configHelper->getAttributeMapping();

        while (($row = $query->fetch())) {
            /* Verify attribute type if already present in Magento */
            /** @var string $attributeFrontendInput */
            $attributeFrontendInput = $connection->fetchOne(
                $connection->select()->from(
                    $this->entitiesHelper->getTable('eav_attribute'),
                    ['frontend_input']
                )->where('attribute_code = ?', $row['code'])
            );
            /** @var bool $skipAttribute */
            $skipAttribute = false;
            if ($attributeFrontendInput && $row['frontend_input']) {
                if ($attributeFrontendInput !== $row['frontend_input'] && !in_array($row['code'],
                        $this->excludedAttributes)) {
                    $skipAttribute = true;
                    /* Verify if attribute is mapped to an ignored attribute */
                    if (is_array($mapping)) {
                        foreach ($mapping as $match) {
                            if (in_array($match['magento_attribute'], $this->excludedAttributes)
                                && $row['code'] == $match['akeneo_attribute']) {
                                $skipAttribute = false;
                            }
                        }
                    }
                }
            }

            if ($skipAttribute === true) {
                /** @var string $message */
                $message = __('The attribute %1 was skipped because its type is not the same between Akeneo and 
                Magento. Please delete it in Magento and try a new import', $row['code']);
                $this->setAdditionalMessage($message);

                continue;
            }

            /* Insert base data (ignore if already exists) */
            /** @var string[] $values */
            $values = [
                'attribute_id'   => $row['_entity_id'],
                'entity_type_id' => $this->getEntityTypeId(),
                'attribute_code' => $row['code'],
            ];
            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('eav_attribute'),
                $values,
                array_keys($values)
            );

            $values = [
                'attribute_id' => $row['_entity_id'],
            ];
            $connection->insertOnDuplicate(
                $this->entitiesHelper->getTable('catalog_eav_attribute'),
                $values,
                array_keys($values)
            );

            /* Retrieve default admin label */
            /** @var string $frontendLabel */
            $frontendLabel = __('Unknown');
            if (!empty($row[$adminLabelColumn])) {
                $frontendLabel = $row[$adminLabelColumn];
            }

            /* Retrieve attribute scope */
            /** @var int $global */
            $global = ScopedAttributeInterface::SCOPE_GLOBAL; // Global
            if ($row['scopable'] == 1) {
                $global = ScopedAttributeInterface::SCOPE_WEBSITE; // Website
            }
            if ($row['localizable'] == 1) {
                $global = ScopedAttributeInterface::SCOPE_STORE; // Store View
            }
            /** @var array $data */
            $data = [
                'entity_type_id' => $this->getEntityTypeId(),
                'attribute_code' => $row['code'],
                'frontend_label' => $frontendLabel,
                'is_global'      => $global,
            ];
            foreach ($columns as $column => $def) {
                if (!$def['only_init']) {
                    $data[$column] = $row[$column];
                }
            }
            /** @var array $defaultValues */
            $defaultValues = [];
            if ($row['_is_new'] == 1) {
                $defaultValues = [
                    'backend_table'                 => null,
                    'frontend_class'                => null,
                    'is_required'                   => 0,
                    'is_user_defined'               => 1,
                    'default_value'                 => null,
                    'is_unique'                     => $row['unique'],
                    'note'                          => null,
                    'is_visible'                    => 1,
                    'is_system'                     => 1,
                    'input_filter'                  => null,
                    'multiline_count'               => 0,
                    'validate_rules'                => null,
                    'data_model'                    => null,
                    'sort_order'                    => 0,
                    'is_used_in_grid'               => 0,
                    'is_visible_in_grid'            => 0,
                    'is_filterable_in_grid'         => 0,
                    'is_searchable_in_grid'         => 0,
                    'frontend_input_renderer'       => null,
                    'is_searchable'                 => 0,
                    'is_filterable'                 => 0,
                    'is_comparable'                 => 0,
                    'is_visible_on_front'           => 0,
                    'is_wysiwyg_enabled'            => 0,
                    'is_html_allowed_on_front'      => 0,
                    'is_visible_in_advanced_search' => 0,
                    'is_filterable_in_search'       => 0,
                    'used_in_product_listing'       => 0,
                    'used_for_sort_by'              => 0,
                    'apply_to'                      => null,
                    'position'                      => 0,
                    'is_used_for_promo_rules'       => 0,
                ];

                /* CUSTOM CODE - START */
                $defaultValues['is_visible_on_front'] = 1;
                if (($row['frontend_input'] == 'multiselect' && $row['backend_type'] == 'varchar')
                    || ($row['frontend_input'] == 'select' && $row['backend_type'] == 'int')) {
                    $defaultValues['is_filterable'] = 1;
                    $defaultValues['is_comparable'] = 1;
                    $defaultValues['is_filterable_in_search'] = 1;
                }
                /* CUSTOM CODE - END */

                foreach (array_keys($columns) as $column) {
                    $data[$column] = $row[$column];
                }
            }

            $data = array_merge($defaultValues, $data);
            $this->eavSetup->updateAttribute(
                $this->getEntityTypeId(),
                $row['_entity_id'],
                $data,
                null,
                0
            );

            /* Add Attribute to group and family */
            if ($row['_attribute_set_id'] && $row['group']) {
                $attributeSetIds = explode(',', $row['_attribute_set_id']);

                if (is_numeric($row['group'])) {
                    $row['group'] = 'PIM' . $row['group'];
                }

                foreach ($attributeSetIds as $attributeSetId) {
                    if (is_numeric($attributeSetId)) {
                        /* Verify if the group already exists */
                        /** @var int $setId */
                        $setId = $this->eavSetup->getAttributeSetId($this->getEntityTypeId(), $attributeSetId);
                        /** @var int $groupId */
                        $groupId = $this->eavSetup->getSetup()->getTableRow(
                            'eav_attribute_group',
                            'attribute_group_name',
                            ucfirst($row['group']),
                            'attribute_group_id',
                            'attribute_set_id',
                            $setId
                        );
                        /** @var bool $akeneoGroup */
                        $akeneoGroup = false;
                        /* Test if the default group was created instead */
                        if (!$groupId) {
//                            $akeneoGroup = true;
                            $groupId     = $this->eavSetup->getSetup()->getTableRow(
                                'eav_attribute_group',
                                'attribute_group_name',
                                ucfirst($row['group']),
                                'attribute_group_id',
                                'attribute_set_id',
                                $setId
                            );
                        }

                        /** @var bool $existingAttribute */
                        /*$existingAttribute = $connection->fetchOne(
                            $connection->select()->from(
                                $this->entitiesHelper->getTable('eav_entity_attribute'),
                                ['COUNT(*)']
                            )->where('attribute_set_id = ?', $setId)->where('attribute_id = ?', $row['_entity_id'])
                        );*/
                        /* The attribute was already imported at least once, skip it */
                        /*if ($existingAttribute) {
                            continue;
                        }*/
                        if ($groupId) {
                            /* The group already exists, update it */
                            /** @var string[] $dataGroup */
                            $dataGroup = [
                                'attribute_set_id'     => $setId,
                                'attribute_group_name' => ucfirst($row['group']),
                            ];
                            if ($akeneoGroup) {
                                $dataGroup = [
                                    'attribute_set_id'     => $setId,
                                    'attribute_group_name' => self::DEFAULT_ATTRIBUTE_SET_NAME,
                                ];
                            }

                            $this->eavSetup->updateAttributeGroup(
                                $this->getEntityTypeId(),
                                $setId,
                                $groupId,
                                $dataGroup
                            );

                            $this->eavSetup->addAttributeToSet(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                $groupId,
                                $row['_entity_id']
                            );
                        } else {
                            /* The group doesn't exists, create it */
                            $this->eavSetup->addAttributeGroup(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                ucfirst($row['group'])
                            );

                            $this->eavSetup->addAttributeToSet(
                                $this->getEntityTypeId(),
                                $attributeSetId,
                                ucfirst($row['group']),
                                $row['_entity_id']
                            );
                        }
                    }
                }
            }

            /* Add store labels */
            /** @var array $stores */
            $stores = $this->storeHelper->getStores('lang');
            /**
             * @var string $lang
             * @var array $data
             */
            foreach ($stores as $lang => $data) {
                if (isset($row['labels-'.$lang])) {
                    /** @var array $store */
                    foreach ($data as $store) {
                        /** @var string $exists */
                        $exists = $connection->fetchOne(
                            $connection->select()->from($this->entitiesHelper->getTable('eav_attribute_label'))->where(
                                'attribute_id = ?',
                                $row['_entity_id']
                            )->where('store_id = ?', $store['store_id'])
                        );

                        if ($exists) {
                            /** @var array $values */
                            $values = [
                                'value' => $row['labels-'.$lang],
                            ];
                            /** @var array $where */
                            $where  = [
                                'attribute_id = ?' => $row['_entity_id'],
                                'store_id = ?'     => $store['store_id'],
                            ];

                            $connection->update($this->entitiesHelper->getTable('eav_attribute_label'),
                                                $values, $where);
                        } else {
                            $values = [
                                'attribute_id' => $row['_entity_id'],
                                'store_id'     => $store['store_id'],
                                'value'        => $row['labels-'.$lang],
                            ];
                            $connection->insert($this->entitiesHelper->getTable('eav_attribute_label'), $values);
                        }
                    }
                }
            }
        }
    }
}
