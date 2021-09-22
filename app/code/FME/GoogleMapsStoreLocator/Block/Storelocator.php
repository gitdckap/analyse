<?php
namespace FME\GoogleMapsStoreLocator\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Storelocator
 * @package FME\GoogleMapsStoreLocator\Block
 */
class Storelocator extends Template
{
    /**
     * @var
     */
    protected $scopeConfig;
    /**
     * @var \FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \FME\GoogleMapsStoreLocator\Helper\Data
     */
    public $googleMapsStoreHelper;

    /**
     * Storelocator constructor.
     * @param Template\Context $context
     * @param \FME\GoogleMapsStoreLocator\Helper\Data $helper
     * @param \FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator\CollectionFactory $collectionFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FME\GoogleMapsStoreLocator\Helper\Data $helper,
        \FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator\CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager
    ) {
        
        $this->collectionFactory = $collectionFactory;
        $this->objectManager = $objectManager;
        $this->googleMapsStoreHelper = $helper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        if ($this->googleMapsStoreHelper->isEnabledInFrontend()) {
            $this->pageConfig->setKeywords($this->googleMapsStoreHelper->getGMapMetaKeywords());
            $this->pageConfig->setDescription($this->googleMapsStoreHelper->getGMapMetadescription());
            $this->pageConfig->getTitle()->set($this->googleMapsStoreHelper->getGMapPageTitle());
  
            return parent::_prepareLayout();
        }
    }

    /**
     * @return mixed
     */
    public function getAllStores()
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('is_active', 1)
        ->setOrder('creation_time', 'ASC');

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getWarehouseDetails()
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('is_active', 1)
            ->setOrder('priority', 'ASC');
        return $collection;
    }
}
