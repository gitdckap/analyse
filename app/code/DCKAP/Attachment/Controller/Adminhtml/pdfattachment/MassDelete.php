<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfattachment;

use Magento\Backend\App\Action;
use Dckap\Attachment\Model\ResourceModel\Pdfattachment\Collection;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected $pdfAttachment;
    protected $collectionFactory;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\Pdfattachment $pdfAttachment,
        Collection $collectionFactory
    ){
        parent::__construct($context);
        $this->pdfAttachment = $pdfAttachment;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pdfattachment');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $attachmentItems = $this->collectionFactory->addFieldToFilter('id', ['in' => $itemIds]);
                foreach ($attachmentItems->getItems() as $item) {
                    $this->deleteById($item);
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
    public function deleteById($item){
        //$post = $this->pdfAttachment->load($itemId);
        $item->delete();
    }
}
