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
namespace Bss\Megamenu\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Megamenu\Model\MenuStoresFactory
     */
    protected $menuStoresFactory;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \Bss\Megamenu\Model\MenuStoresFactory $menuStoresFactory
     */
    public function __construct(
        Action\Context $context,
        \Bss\Megamenu\Model\MenuStoresFactory $menuStoresFactory
    ) {
        parent::__construct($context);
        $this->menuStoresFactory = $menuStoresFactory;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $modelFactory = $this->menuStoresFactory->create();
                $modelFactory->load($id);
                $modelFactory->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('The store menu has been deleted.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/index');
    }
}
