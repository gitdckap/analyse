<?php

namespace DCKAP\Categorywidget\Block\Widget;
 
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\CategoryRepository;
 
class Showcategories extends \Magento\Framework\View\Element\Template implements BlockInterface
{
 
    protected $_template = 'widget/showcategories.phtml';
    protected $categoryRepository;
    protected $_categoryCollectionFactory;
    protected $_storeManager;
     /**
      * @var ImageFactory
      */
    protected $helperFactory;
    protected $scopeConfig;
 
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ImageFactory $helperFactory,
        CategoryRepository $categoryRepository,
        CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\CategoryListInterface $categoryList,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
    ) {
 
        $this->_storeManager = $storeManager;
        $this->helperFactory = $helperFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->categoryList=$categoryList;
        $this->searchCriteriaBuilder=$searchCriteriaBuilder;
        $this->filterBuilder=$filterBuilder;
        $this->filterGroupBuilder=$filterGroupBuilder;
        parent::__construct($context);
    }
 
    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }
 
    /**
     * Retrieve Category ids
     *
     * @return string
     */
    public function getCategoryIds()
    {
        if ($this->hasData('categoryids')) {
            return $this->getData('categoryids');
        }
        return $this->getData('categoryids');
    }
 
    /**
     *  Get the category collection based on the ids
     *
     * @return array
     */
    public function getCategoryCollection()
    {
        $store = $this->_storeManager->getStore($this->_storeManager->getStore()->getId());
        $storeCode = $store->getCode();
         $sizecount=8;
        if ($storeCode != "default") {
            $sizecount = 6;
        }
        $rootCategoryId=$this->_storeManager->getStore()->getRootCategoryId();
        $featuredFilter=$this->filterBuilder->setField('is_feature')->setValue('1')->setConditionType("eq")->create();
        $activeFilter=$this->filterBuilder->setField('is_active')->setValue('1')->setConditionType("eq")->create();
        $storeFilter=$this->filterBuilder->setField('path')
        ->setValue("1/{$rootCategoryId}/%")->setConditionType("like")->create();
        $group1=$this->filterGroupBuilder->addFilter($featuredFilter)->create();
        $group2=$this->filterGroupBuilder->addFilter($activeFilter)->create();
        $group3=$this->filterGroupBuilder->addFilter($storeFilter)->create();
        $searchCriteria=$this->searchCriteriaBuilder->setFilterGroups([$group1,$group2,$group3])->create();
        $searchCriteria->setPageSize($sizecount)->setCurrentPage(1);
        $categorylistresults=$this->categoryList->getList($searchCriteria);
        $categories=$categorylistresults->getItems();
        return $categories;
    }

    /**
     * @param $categoryId
     * @return string
     */
    public function getCategoryImageUrl($categoryId)
    {
        $categoryDetail = $this->categoryRepository->get($categoryId);
        if ($categoryDetail->getImageUrl()) {
            return $categoryDetail->getImageUrl();
        } else {
            $currentStore = $this->_storeManager->getStore();
            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $holder = 'catalog/product/placeholder/';
            return $mediaUrl.$holder.$this->_scopeConfig->getValue('catalog/placeholder/image_placeholder');
//            return $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/image.jpg');
        }
    }
    public function getImage($product, $imageId, $attributes = [])
    {
        $image = $this->helperFactory->create()->init($product, $imageId)
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepTransparency(true)
            ->keepFrame(false)
            ->resize(200, 300);

        return $image;
    }

    public function getFeaturedCategories()
    {
        $rootCategoryId=$this->_storeManager->getStore()->getRootCategoryId();
        $featuredFilter=$this->filterBuilder->setField('is_feature')->setValue('1')
            ->setConditionType("eq")->create();
        $activeFilter=$this->filterBuilder->setField('is_active')->setValue('1')
            ->setConditionType("eq")->create();
        $storeFilter=$this->filterBuilder->setField('path')->setValue("1/{$rootCategoryId}/%")
            ->setConditionType("like")->create();
        $group1=$this->filterGroupBuilder->addFilter($featuredFilter)->create();
        $group2=$this->filterGroupBuilder->addFilter($activeFilter)->create();
        $group3=$this->filterGroupBuilder->addFilter($storeFilter)->create();
        $searchCriteria=$this->searchCriteriaBuilder
            ->setFilterGroups([$group1,$group2,$group3])->create();
        $searchCriteria->setPageSize(8)->setCurrentPage(1);
        $categorylistresults=$this->categoryList->getList($searchCriteria);
        $categories=$categorylistresults->getItems();
        return $categories;
        /*
        $collection = $this->_categoryCollectionFactory->create()
             ->addFieldToFilter('is_feature',1)
             ->addFieldToFilter('is_active',1)
             ->addAttributeToSelect(['name', 'url'])
             ->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
             ->setPageSize(8)
             ->setCurPage(1);
         return $collection;
        */
    }

    public function getBaseStore()
    {
        return $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
}
