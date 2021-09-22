<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Controller\Adminhtml\Storelocator;

use Magento\Backend\App\Action;

/**
 * Class Delete
 * @package FME\GoogleMapsStoreLocator\Controller\Adminhtml\Storelocator
 */
class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'FME_GoogleMapsStoreLocator::storelocator_delete';

    /**
     * @var \FME\GoogleMapsStoreLocator\Model\Storelocator
     */
    protected $model;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param \FME\GoogleMapsStoreLocator\Model\Storelocator $model
     */
    public function __construct(
        Action\Context $context,
        \FME\GoogleMapsStoreLocator\Model\Storelocator $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('gmaps_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getTitle();
                $this->model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The store has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_storelocatorpage_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_storelocatorpage_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['gmaps_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a store to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
