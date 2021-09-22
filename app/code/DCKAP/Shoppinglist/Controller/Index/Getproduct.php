<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

class Getproduct extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var  \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var  \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function execute()
    {

        $query = $this->getRequest()->getParam('query');

        $this->productCollection->addAttributeToSelect(
            ['name','price','sku','id','type_id','small_image']
        )
                                ->addAttributeToFilter('visibility', ['neq'=> 1])
                                ->addAttributeToFilter(
                                    [
                                            ['attribute'=> 'name','like'=> '%'.$query.'%'],
                                            ['attribute'=> 'sku','like'=> '%'.$query.'%']
                                        ]
                                )
                                ->load();

        $productData = [];
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
        foreach ($this->productCollection as $productObj) {
            $productData[] = [
                                'label' => $productObj->getName(),
                                'title' => $productObj->getName(),
                                'productid' => $productObj->getId(),
                                'sku'   => $productObj->getSKU(),
                                'price' => $productObj->getPrice(),
                                'ptype'  => $productObj->getTypeId(),
                                'pimage' => $mediaUrl.$productObj->getSmallImage()
                            ];
        }

        if (empty($productData)) {
            $productData[] = [
                                'label' => __('No matches found'),
                                'title' => __('No matches found'),
                                'value' => 'no-matches'
                            ];
        }
        
        return $this->resultJsonFactory->create()->setData($productData);
    }
}
