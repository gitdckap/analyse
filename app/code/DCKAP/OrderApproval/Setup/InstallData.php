<?php
namespace DCKAP\OrderApproval\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
   public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Install messages
         */
        $arrmixSalesOrderStatusData = [
            [
                'status' => 'pending_approval',
                'label' => 'Pending Approval'
            ],
            [
                'status' => 'declined',
                'label' => 'Declined'
            ],
            [
                'status' => 'customer_cancelled',
                'label' => 'Customer Cancelled'
            ],
            [
                'status' => 'approved',
                'label' => 'Approved'
            ]
        ];
        foreach ($arrmixSalesOrderStatusData as $bind) {
            $setup->getConnection()
                ->insertForce($setup->getTable('sales_order_status'), $bind);
        }
        $arrmixSalesOrderStatusStateData =  [
            [
                'status' => 'pending_approval',
                'state' => 'pending_approval',
                'is_default' => '0',
                'visible_on_front' => 1
            ],
            [
                'status' => 'declined',
                'state' => 'declined',
                'is_default' => '0',
                'visible_on_front' => 1
            ],
            [
                'status' => 'customer_cancelled',
                'state' => 'customer_cancelled',
                'is_default' => '0',
                'visible_on_front' => 1
            ],
            [
                'status' => 'approved',
                'state' => 'approved',
                'is_default' => '0',
                'visible_on_front' => 1
            ]
        ];
        foreach ($arrmixSalesOrderStatusStateData as $bind) {
            $setup->getConnection()
                ->insertForce($setup->getTable('sales_order_status_state'), $bind);
        }
    }
}

