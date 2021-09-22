<?php

namespace DCKAP\Extension\Model;

/**
 * @api
 * @since 100.0.2
 */
class Yesno implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Never')], ['value' => 1, 'label' => __('Status Only')], ['value' => 2,
            'label' => __('Show Available Quantity')], ['value' => 3, 'label' => __('Show Warehouse Inventory')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Never'), 1 => __('Status Only'), 2 => __('Show Available Quantity'),
            3 => __('Show Warehouse Inventory')];
    }
}
