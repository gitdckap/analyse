<?php
namespace Dckap\Theme\Model\Config;

class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'b2b', 'label' => __('B2B')],
            ['value' => 'b2c', 'label' => __('B2C')],
        ];
    }
}
