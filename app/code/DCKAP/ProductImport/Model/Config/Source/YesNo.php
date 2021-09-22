<?php
namespace Dckap\ProductImport\Model\Config\Source;

/**
 * Class YesNo
 * @package Dckap\ProductImport\Model\Config\Source
 */
class YesNo extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var
     */
    protected $_options;

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        }
        return $this->_options;
    }

    /**
     * @return array
     */
    final public function toOptionArray()
    {
       return [
        ['value' => '0', 'label' => __('No')],
        ['value' => '1', 'label' => __('Yes')]
    ];
   }
}