<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{

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

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->pdfSection->load($id);
                $this->pdfSection->setCreatedAt(date('Y-m-d H:i:s'));
            }

            $this->pdfSection->setData($data);

            try {
                $this->pdfSection->save();
                $this->messageManager->addSuccess(__('The Pdfsection has been saved.'));
                $this->session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->pdfSection->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Pdfsection.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
