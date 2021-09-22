<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfattachment;

use Magento\Backend\App\Action;
use Dckap\Attachment\Model\ResourceModel\Pdfattachment\Collection;

/**
 * Class MassStatus
 * @package Dckap\Attachment\Controller\Adminhtml\pdfattachment
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Update blog post(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    protected $pdfSection;
    /**
     * @var
     */
    protected $session;
    /**
     * @var CollectionFactory
     */
    protected $attachmencollection;

    /**
     * MassStatus constructor.
     * @param Action\Context $context
     * @param \Dckap\Attachment\Model\Pdfattachment $pdfAttachment
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\Pdfattachment $pdfAttachment,
        Collection $collection
    ){
        parent::__construct($context);
        $this->pdfAttachment = $pdfAttachment;
        $this->attachmencollection = $collection;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('pdfattachment');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addError(__('Please select item(s).'));
        } else {
            try {
                $status = (int) $this->getRequest()->getParam('status');
                $collectionItems = $this->attachmencollection->addFieldToFilter('id', ['in'=>$itemIds]);

                foreach ($collectionItems->getItems() as $post) {
                    $this->setActiveById($post,$status);
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

    /**
     * @param $postId
     * @param $status
     */
    public function setActiveById($post, $status){
        //$post = $this->pdfAttachment->load($postId);
        $post->setIsActive($status)->save();
    }
}
