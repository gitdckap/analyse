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

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $model;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\CheckoutCustomField\Model\Attribute $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\CheckoutCustomField\Model\Attribute $model
    ) {
        parent::__construct($context);
        $this->model = $model;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model \Bss\Catalog\CheckoutCustomField\Model\Attribute */
        if ($id) {
            $this->model->load($id);
            $resultRedirect = $this->resultRedirectFactory->create();
            $attrName = $this->model->getBackendLabel();
            if (!$this->model->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                return $resultRedirect->setPath('bss_customfield/*/');
            }
            $this->model->delete();
            $this->_eventManager->dispatch(
                'bss_customfield_delete',
                ['attribute'=>$this->model]
            );
            $this->messageManager->addSuccessMessage(
                __('The attribute %1 have been deleted.', $attrName)
            );
            return $resultRedirect->setPath('bss_customfield/*/');
        }
    }
}
