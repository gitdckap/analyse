<?php
namespace DCKAP\OrderApproval\Setup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if($context->getVersion() < '1.0.3') {
            $arrmixSalesOrderStatusStateData =  [
                [
                    'status' => 'pending',
                    'state' => 'pending',
                    'is_default' => '0',
                    'visible_on_front' => 1
                ]
            ];
            foreach ($arrmixSalesOrderStatusStateData as $bind) {
                $setup->getConnection()->insertForce($setup->getTable('sales_order_status_state'), $bind);
            }
        }

        if($context->getVersion() < '1.0.4') {
            $arrmixSalesOrderStatusData = [
                [
                    'status' => 'edited_by_approver',
                    'label' => 'Edited By Approver'
                ]
            ];
            foreach ($arrmixSalesOrderStatusData as $bind) {
                $setup->getConnection()
                    ->insertForce($setup->getTable('sales_order_status'), $bind);
            }
            $arrmixSalesOrderStatusStateData =  [
                [
                    'status' => 'edited_by_approver',
                    'state' => 'edited_by_approver',
                    'is_default' => '0',
                    'visible_on_front' => 1
                ]
            ];
            foreach ($arrmixSalesOrderStatusStateData as $bind) {
                $setup->getConnection()->insertForce($setup->getTable('sales_order_status_state'), $bind);
            }
        }
    }
}
