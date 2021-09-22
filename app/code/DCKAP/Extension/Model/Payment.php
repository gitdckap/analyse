<?php

namespace DCKAP\Extension\Model;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayInterface;
use \Magento\Payment\Model\Config;
use Akeneo\Connector\Helper\Import\Entities;
use Zend\Json\Expr;

/**
 * Class Payment
 * @package DCKAP\Extension\Model
 */
class Payment extends DataObject implements ArrayInterface
{
    protected $logger;
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_paymentModelConfig;
    /**
     * @var Entities
     */
    protected $entities;

    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        Entities $entitiesHelper
    ) {
        $this->logger                = $logger;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->entities = $entitiesHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $payments = $this->_paymentModelConfig->getActiveMethods();
        $methods = [];
        $methods[0] = [
            'label' => 'Select an option',
            'value' => ''
        ];
        foreach ($payments as $paymentCode => $paymentModel) {
            $this->logger->info('Payments Response Value : ',$payments);
            if ($paymentCode == 'anet_creditcard' || $paymentCode == 'authorizenet_acceptjs' || $paymentCode == 'elementpayment' || $paymentCode == 'payflowpro') {
                $paymentTitle = $this->_appConfigScopeConfigInterface
                    ->getValue('payment/' . $paymentCode . '/title');
                $methods[$paymentCode] = [
                    'label' => $paymentTitle,
                    'value' => $paymentCode
                ];
            }
        }
        return $methods;
    }

    /**
     * @param $table
     * @return array
     */
    public function getCategoriesList($table)
    {
        $connection = $this->entities->getConnection();
        $select = $connection->select()->from($table)->where('code = ?', 'default_category')
            ->where('import = ?', 'category');
        return $connection->fetchAll($select);
    }

    /**
     * @param $pimEntitiesTable
     * @param $res
     * @return int
     */
    public function insertEntitiesById($pimEntitiesTable, $res)
    {
        $connection = $this->entities->getConnection();
        $result = $connection->update($pimEntitiesTable, $res, ['id = ?' => (int)$res['id']]);
        return $result;
    }

    /**
     * @param $pimEntitiesTable
     * @param $res
     * @return int
     */
    public function insertEntities($pimEntitiesTable, $res)
    {
        $connection = $this->entities->getConnection();
        $result = $connection->insert($pimEntitiesTable, $res);
        return $result;
    }

    /**
     * @param $table
     * @return array
     */
    public function getTableData($table)
    {
        $connection = $this->entities->getConnection();
        $sql = $connection->select()->from($table);
        $result = $connection->fetchAll($sql);
        return $result;
    }

    /**
     * @param $tmpTable
     * @return \Zend_Db_Statement_Interface
     */
    public function getConfigurableSelect($tmpTable)
    {
        $connection = $this->entities->getConnection();
        $configurableSelect = $connection->select()->from($tmpTable, ['_entity_id', '_axis', '_children'])
            ->where('_type_id = ?', 'configurable')->where('_axis IS NOT NULL')->where('_children IS NOT NULL');
        $query = $connection->query($configurableSelect);
        return $query;
    }

    /**
     * @param $id
     * @return bool
     */
    public function checkOptions($id)
    {
        $connection = $this->entities->getConnection();
        $result = (bool)$connection->fetchOne(
                    $connection->select()->from($this->entities->getTable('eav_attribute_option'),
                        [new Expr(1)])->where('attribute_id = ?', $id)->limit(1)
                );
        return $result;
    }

    /**
     * @param $id
     * @param $rowEntityId
     * @return string
     */
    public function getSuperAttributeId($id, $rowEntityId)
    {
        $connection = $this->entities->getConnection();
        $result = $connection->fetchOne(
                    $connection->select()->from($this->entities->getTable('catalog_product_super_attribute'))
                        ->where('attribute_id = ?', $id)->where('product_id = ?', $rowEntityId)->limit(1)
                );
        return $result;
    }

    /**
     * @param $child
     * @return int
     */
    public function getChildId($child)
    {
        $connection = $this->entities->getConnection();
        $childId = (int)$connection->fetchOne(
                        $connection->select()->from($this->entities->getTable('catalog_product_entity'), ['entity_id'])
                            ->where('sku = ?', $child)->limit(1)
                    );
        return $childId;
    }
}
