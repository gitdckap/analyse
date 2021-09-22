<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutCustomField\Model\Config\Source;

class Inputtype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'text', 'label' => __('Text Field')],
            ['value' => 'textarea', 'label' => __('Text Area')],
            ['value' => 'date', 'label' => __('Date & Time')],
            ['value' => 'boolean', 'label' => __('Yes/No')],
            ['value' => 'multiselect', 'label' => __('Checkbox')],
            ['value' => 'dropdown', 'label' => __('Dropdown')],
            ['value' => 'select', 'label' => __('Radio Button')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return[
            'text' => __('Text Field'),
            'textarea' => __('Text Area'),
            'date' => __('Date & Time'),
            'boolean' => __('Yes/No'),
            'multiselect' => __('Checkbox'),
            'dropdown' => __('Dropdown'),
            'select' => __('Radio Button'),
        ];
    }
}
