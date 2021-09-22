<?php
namespace Emizentech\ShopByBrand\Block;
class Index extends \Magento\Framework\View\Element\Template
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

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Shop By Brand'));
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getBrands(){

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
//        echo "<pre>"; print_r($attrval); exit;
        $collection = $this->_brandFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('attribute_id', array('in'=>$attrval));
        $collection->setOrder('name', 'ASC');
        $charBarndArray = array();
        foreach ($collection as $brand) {
           $name = trim($brand->getName());
           $charBarndArray[strtoupper($name[0])][] = array_unique($brand->getData());
        }
//        echo "<pre>"; print_r($charBarndArray); exit;
        return $charBarndArray;
    }

    public function getImageMediaPath(){
        return $this->getUrl('pub/media',['_secure' => $this->getRequest()->isSecure()]);
    }

    public function getFeaturedBrands(){
        //  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//     	$model = $objectManager->create(
//             'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
//         )->setEntityTypeId(
//             \Magento\Catalog\Model\Product::ENTITY
//         );
// 
// 		$model->loadByCode(\Magento\Catalog\Model\Product::ENTITY,'manufacturer');
// 		return $model->getOptions();

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
        $collection->addFieldToFilter('featured' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('attribute_id', array('in'=>$attrval));
        $collection->setOrder('sort_order' , 'ASC');
        return $collection;
    }

}