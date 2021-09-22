<?php
namespace Emizentech\ShopByBrand\Block\Widget;
use Magento\Widget\Block\BlockInterface; 
class Index extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    protected $_storeManager;
    protected $_brandFactory;
    protected $helperData;
    protected $_template = "widget/brandslider.phtml";


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Emizentech\ShopByBrand\Model\BrandFactory $brandFactory,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Mageplaza\Productslider\Helper\Data $helperData
    ) 
    {
    	 $this->_brandFactory = $brandFactory;
         $this->_storeManager = $storeManager;
         $this->helperData = $helperData;
        parent::__construct($context);
    }
  
     public function getImageMediaPath(){
    	return $this->getUrl('pub/media',['_secure' => $this->getRequest()->isSecure()]);
    }
    
     public function getFeaturedBrands(){

		$collection = $this->_brandFactory->create()->getCollection();
		$collection->addFieldToFilter('is_active' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
		$collection->addFieldToFilter('featured' , \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED);
		$collection->setOrder('sort_order' , 'ASC');
    	return $collection;
    }
    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager
                         ->getStore()
                         ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
     public function getOptions()
    {
        $producttype="brand";
        return $this->helperData->getAllOptions($producttype);
    }   
}