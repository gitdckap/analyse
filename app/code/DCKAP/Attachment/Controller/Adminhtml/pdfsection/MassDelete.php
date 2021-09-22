<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{


    protected $pdfSection;
    protected $session;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\Pdfsection $pdfSection
    ){
        parent::__construct($context);
        $this->pdfSection = $pdfSection;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pdfsection');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $this->deletePdfById($itemId);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('attachment/*/index');
    }

    public function deletePdfById($itemId)
    {
        $post = $this->pdfSection->load($itemId);
        $post->delete();
    }
}
