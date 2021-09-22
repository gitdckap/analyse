<?php
namespace DCKAP\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class View extends AbstractHelper
{
    protected $_groupCollection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $_groupCollection
    ) {
        $this->_groupCollection = $_groupCollection;
        parent::__construct($context);
    }

    public function getAttributeGroupId($attributeSetId)
    {
        $groupCollection = $this->_groupCollection->create();
        $groupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
        $groupCollection->addFieldToFilter('attribute_group_name', 'Grid Attributes');
        return $groupCollection->getFirstItem();
    }

    public function getAttributeGroups($attributeSetId)
    {
        $groupCollection = $this->_groupCollection->create();
        $groupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
        $groupCollection->setOrder('sort_order', 'ASC');
        $groupCollection = $groupCollection->getData();

        $defaultGroupCollection = $this->_groupCollection->create();
        $defaultGroupCollection->addFieldToFilter('attribute_set_id', 4);
        $defaultGroupCollection->setOrder('sort_order', 'ASC');

        foreach ($defaultGroupCollection->getData() as $defaultGroup) {
            foreach ($groupCollection as $key => $group) {
                if ($defaultGroup['attribute_group_code'] == $group['attribute_group_code']) {
                    unset($groupCollection[$key]);
                }
            }
        }

        $newGroupCollection = [];
        foreach ($groupCollection as $key => $group) {
            $newGroupCollection[$group['attribute_group_code']] = $group;
        }

        return $newGroupCollection;
    }

    public function getGroupAttributes($pro, $groupId, $productAttributes)
    {
        $data=[];
        $no =__('No');
        foreach ($productAttributes as $attribute) {
            if ($attribute->isInGroup($pro->getAttributeSetId(), $groupId) && $attribute->getIsVisibleOnFront()) {
                if ($attribute->getFrontend()->getValue($pro) && $attribute->getFrontend()->getValue($pro)!='' && $attribute->getFrontend()->getValue($pro)!=$no) {
                    $data[]=$attribute;
                }
            }
        }
        return $data;
    }
}
