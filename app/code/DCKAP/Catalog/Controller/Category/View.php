<?php

namespace DCKAP\Catalog\Controller\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class View extends \Magento\Catalog\Controller\Category\View
{

    private $layerResolver;

    private $toolbarMemorizer;

    public function __construct(
        Context $context,
        Design $catalogDesign,
        Session $catalogSession,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        ToolbarMemorizer $toolbarMemorizer = null
    ) {
        $this->layerResolver = $layerResolver;
        $this->toolbarMemorizer = $toolbarMemorizer ?: $context->getObjectManager()->get(ToolbarMemorizer::class);
        parent::__construct($context, $catalogDesign, $catalogSession, $coreRegistry, $storeManager, $categoryUrlPathGenerator, $resultPageFactory, $resultForwardFactory, $layerResolver, $categoryRepository, $toolbarMemorizer);
    }

    public function execute()
    {
        if ($this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        $category = $this->_initCategory();
        if ($category) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->_catalogSession->setLastViewedCategoryId($category->getId());

            $page = $this->resultPageFactory->create();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $page->getConfig()->setPageLayout($settings->getPageLayout());
            }

            $hasChildren = $category->hasChildren();
            if ($category->getIsAnchor()) {
                $type = $hasChildren ? 'layered' : 'layered_without_children';
            } else {
                $type = $hasChildren ? 'default' : 'default_without_children';
            }

            if (!$hasChildren) {
                // Two levels removed from parent.  Need to add default page type.
                $parentType = strtok($type, '_');
                $page->addPageLayoutHandles(['type' => $parentType], null, false);
            }
            $page->addPageLayoutHandles(['type' => $type], null, false);
            $page->addPageLayoutHandles(['id' => $category->getId()]);

            // apply custom layout update once layout is loaded
            $layoutUpdates = $settings->getLayoutUpdates();
            if ($layoutUpdates && is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $page->addUpdate($layoutUpdate);
                    $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
                }
            }

            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($category))
                ->addBodyClass('category-' . $category->getUrlKey());

            if ($hasChildren && !$this->_request->getParam('manufacturer')) {
                $page->getConfig()->setPageLayout('1column');
                $page->getLayout()->unsetElement('sidebar.main');
                $page->getLayout()->unsetElement('sidebar.additional');
            }

            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
