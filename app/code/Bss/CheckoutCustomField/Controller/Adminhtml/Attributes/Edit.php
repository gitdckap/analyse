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
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutCustomField\Controller\Adminhtml\Attributes;

use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $model;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param \Bss\CheckoutCustomField\Model\Attribute $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory,
        \Bss\CheckoutCustomField\Model\Attribute $model
    ) {
        $this->model = $model;
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
    }
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->model->load($id);

            if (!$this->model->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('bss_customfield/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getAttributeData(true);
        if (!empty($data)) {
            $this->model->addData($data);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $this->model->addData($attributeData);
        }

        $this->_coreRegistry->register('entity_attribute', $this->model);

        $item = $id ? __('Edit Checkout Field') : __('New Checkout Field');

        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($id ? $this->model->getBackendLabel() : __('New Checkout Field'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CheckoutCustomField::bss_order_attributes');
    }
}
