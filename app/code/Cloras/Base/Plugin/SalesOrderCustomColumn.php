<?php
namespace Cloras\Base\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\Registry;

class SalesOrderCustomColumn
{
    private $messageManager;
    private $collection;
    private $registry;

    public function __construct(MessageManager $messageManager,
                                SalesOrderGridCollection $collection,Registry $registry
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->registry = $registry;
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof $this->collection
            ) {
                if (is_null($this->registry->registry('ddi_order_id'))) {
                    $select = $this->collection->getSelect();
                    $select->joinLeft(
                        ["sorder" => "sales_order"],
                        'main_table.increment_id = sorder.increment_id',
                        array('ddi_order_id')
                    );
                    $this->registry->register('ddi_order_id', true);
                }
                return $this->collection;
            }
        }
        return $result;
    }
}
