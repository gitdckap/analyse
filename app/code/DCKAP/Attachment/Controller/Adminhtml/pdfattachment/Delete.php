<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfattachment;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected $pdfAttachment;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\Pdfattachment $pdfAttachment
    ){
        parent::__construct($context);
        $this->pdfAttachment = $pdfAttachment;
    }

    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $this->pdfAttachment->load($id);
                $this->pdfAttachment->delete();
                // display success message
                $this->messageManager->addSuccess(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
