<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    protected $pdfSection;
    protected $session;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\Pdfsection $pdfSection,
        \Magento\Backend\Model\Session $session
    ){
        parent::__construct($context);
        $this->pdfSection = $pdfSection;
        $this->session = $session;

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
                $this->pdfSection->load($id);
                $this->pdfSection->delete();
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
