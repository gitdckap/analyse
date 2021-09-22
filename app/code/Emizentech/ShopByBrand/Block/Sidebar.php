<?php
namespace Emizentech\ShopByBrand\Block;
class Sidebar extends \Magento\Framework\View\Element\Template
{

    protected $_brandFactory;
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Emizentech\ShopByBrand\Model\BrandFactory $brandFactory,
         \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) 
    {
    	 $this->_brandFactory = $brandFactory;
         $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }
    
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getBrands(){
		$collection = $this->_brandFactory->create()->getCollection();
		$collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
		$collection->setOrder('name' , 'ASC');
        $collection->setPageSize(10)->setCurPage(1)->load();
		$charBarndArray = array();
		foreach($collection as $brand)
		{	
			$name = trim($brand->getName());
			$charBarndArray[strtoupper($name[0])][] = $brand->getData();
		}
		
    	return $charBarndArray;
    }

    public function getFooterBrands()
    {
      /*
        $product = $this->_productCollectionFactory->create();
        $product = $product->addAttributeToSelect('manufacturer');
        $product->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $attrval = array();
        foreach($product as $val)
        {
            if($val->getManufacturer())
            {
                $attrval[] = $val->getManufacturer();
            }
        }
        $collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('attribute_id', array('in'=>$attrval));
        $collection->setOrder('name' , 'ASC');
        $collection->setPageSize(10)->setCurPage(1)->load();
        return $collection;
*/
$collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->setOrder('name' , 'ASC');
        $collection->setPageSize(10)->setCurPage(1)->load();
        return $collection;

    }
    
}
