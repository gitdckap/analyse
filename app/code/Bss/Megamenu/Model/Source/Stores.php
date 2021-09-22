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
 * @package    Bss_MegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Megamenu\Model\Source;

class Stores implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Group\CollectionFactory
     */
    protected $storeGroups;

    /**
     * Stores constructor.
     * @param \Magento\Store\Model\ResourceModel\Group\CollectionFactory $storeGroups
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $storeGroups
    ) {
        $this->storeGroups = $storeGroups;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['label' => __('All Stores'), 'value' => 0]];
        $storeGroups = $this->storeGroups->create();
        if ($storeGroups->getSize()) {
            foreach ($storeGroups as $item) {
                $options[] = ['label' => $item->getName(), 'value' => $item->getId()];
            }
        }

        return $options;
    }
}
