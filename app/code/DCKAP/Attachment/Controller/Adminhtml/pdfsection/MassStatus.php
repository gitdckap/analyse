<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */

    public function __construct(
        Context $context,
        \Dckap\Attachment\Model\Pdfsection $pdfsection
    ) {
        parent::__construct($context);

        $this->_pdfSection = $pdfsection;
    }

    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pdfsection');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                foreach ($itemIds as $postId) {
                    $this->setActive($postId, $status);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('attachment/*/index');
    }

    private function setActive($postId, $status)
    {
        $post = $this->_pdfSection->load($postId);
        $post->setIsActive($status)->save();
    }
}
