<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\QuickRFQ\Block\Customer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Dckap_QuickRFQ::orderpad.phtml';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var CollectionFactoryInterface
     */
    private $orderCollectionFactory;

    protected $orderFactory;
    protected $productCollectionFactory;
    protected $productRepository;
    protected $categorycollectionFactory;
    protected $orderItem = array();
    protected $orderData = array();
    protected $themeHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categorycollectionFactory,
        \Dckap\Theme\Helper\Data $themeHelper,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->orderFactory = $orderFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->categorycollectionFactory = $categorycollectionFactory;
        $this->themeHelper = $themeHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Order Pad'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProducts()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $orderCollection = $this->_orderCollectionFactory->create();
        $orderCollection->addAttributeToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'desc');
        $orderCollection->getSelect()->limit(10);
        $products = array();
        foreach ($orderCollection as $order) {
            $orderData = $this->orderFactory->create()->load($order->getId());
            if (!isset($this->orderData[$order->getId()])) {
                $this->orderData[$order->getId()] = $orderData->getStatus();
            }
            $orderItems = $orderData->getAllVisibleItems();
            if ($orderItems && count($orderItems)) {
                foreach ($orderItems as $orderItem) {
                    $products[$orderItem->getProductId()] = $orderItem->getSku();
                    if (!isset($this->orderItem[$orderItem->getProductId()])) {
                        $this->orderItem[$orderItem->getProductId()] = $orderItem->getData();
                    }
                }
            }
        }
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $products));
        return $collection;
    }

    public function getProductDetails($sku = false)
    {
        if ($sku) {
            return $this->productRepository->get($sku);
        }
        return false;
    }

    public function getOrderItemDetails()
    {
        return $this->orderItem;
    }

    public function getOrderDataDetails()
    {
        return $this->orderData;
    }

    public function getCategoryName($ids = false)
    {
        if ($ids) {
            $catIds = explode(',', $ids);
            $cat = array();
            $categoryCollection = $this->categorycollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $catIds));
            if ($categoryCollection && $categoryCollection->getSize()) {
                foreach ($categoryCollection as $category) {
                    $cat[$category->getId()] = $category->getName();
                }
            }
            return $cat;
        }
        return false;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pager'
            )->setCollection(
                $this->getProducts()
            );
            $this->setChild('pager', $pager);
            $this->getProducts()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function isDisplayed()
    {
        return $this->themeHelper->getOrderPadView();
    }
}
