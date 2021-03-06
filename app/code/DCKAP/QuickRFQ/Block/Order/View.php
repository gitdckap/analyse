<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DCKAP\QuickRFQ\Block\Order;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $productRepository;
    protected $themeHelper;
    protected $_registry;
    protected $imageHelperFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \DCKAP\Theme\Helper\Data $themeHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->themeHelper = $themeHelper;
        $this->_registry = $registry;
        $this->imageHelperFactory = $imageHelperFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Order View'));
    }

    public function getDdiOrder()
    {
        $order = $this->_registry->registry('ddi_order');
        return $order;
    }

    public function getProductImageUrl($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            if ($product) {
                $imageUrl = $this->imageHelperFactory->create()->init($this->productRepository->get($sku), 'product_thumbnail_image')->getUrl();
                return $imageUrl;
            }
        } catch (\Exception $e) {
            return '';
        }
        return '';
    }

}
