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
namespace Bss\Megamenu\Block\Adminhtml\Category;

class Tree extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\Megamenu\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Bss\Megamenu\Model\MenuFactory
     */
    protected $modelMenuFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\Megamenu\Model\ConfigFactory
     */
    protected $configFactory;

    /**
     * @var \Bss\Megamenu\Model\MenuStoresFactory
     */
    protected $menuStoresFactory;

    /**
     * Tree constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Bss\Megamenu\Helper\Data $helper
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param \Bss\Megamenu\Model\MenuFactory $modelMenuFactory
     * @param \Bss\Megamenu\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Bss\Megamenu\Model\MenuStoresFactory $menuStoresFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Bss\Megamenu\Helper\Data $helper,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \Bss\Megamenu\Model\MenuFactory $modelMenuFactory,
        \Bss\Megamenu\Model\ConfigFactory $configFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Bss\Megamenu\Model\MenuStoresFactory $menuStoresFactory
    ) {
        $this->helper = $helper;
        $this->configFactory = $configFactory;
        $this->urlBuilder = $urlBuilder;
        $this->modelMenuFactory = $modelMenuFactory;
        $this->resource = $resource;
        $this->scopeConfig = $context->getScopeConfig();
        $this->menuStoresFactory = $menuStoresFactory;
        parent::__construct($context);
    }

    /**
     * @return \Bss\Megamenu\Helper\Data
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return mixed|string
     */
    public function menuTree()
    {
        $modelId = $this->getRequest()->getParam('id');
        $store = $this->menuStoresFactory->create();
        $storeModel = $store->load($modelId);

        if (!$storeModel) {
            return $this->getDefaultMenu();
        }

        $storeId = $storeModel->getStoreId();

        if ($storeId == null) {
            $storeId = 0;
        }

        $menu = $this->getHelperData()->getMegaMenuConfig($storeId);

        if ($menu == '' || $menu == 'false') {
            $menu = $this->getDefaultMenu();
        }

        return $menu;
    }

    /**
     * @return string
     */
    protected function getDefaultMenu()
    {
        return '[{ "text" : "Root Menu", "id" : "root"}]';
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $cacheTypes = 'Page Cache';
        $message = __('One or more of the Cache Types are invalidated: %1. ', $cacheTypes) . ' ';
        $url = $this->urlBuilder->getUrl('adminhtml/cache');
        $message .= __("Please go to <a href='%1'>Cache Management</a> and refresh cache types.", $url);
        return $message;
    }

    /**
     * @param $type
     * @return string
     */
    public function getNodeUrl($type)
    {
        return $this->urlBuilder->getUrl('megamenu/category/create', $paramsHere = ['type' => $type]);
    }

    /**
     * @param $type
     * @return string
     */
    public function getMenuItemUrl($type)
    {
        return $this->urlBuilder->getUrl('megamenu/item/edit', $paramsHere = ['type' => $type]);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $modelId = $this->getRequest()->getParam('id');
        $store = $this->menuStoresFactory->create();
        $storeModel = $store->load($modelId);
        if (!$storeModel) {
            return '';
        }

        return $storeModel->getStoreId();
    }
}
