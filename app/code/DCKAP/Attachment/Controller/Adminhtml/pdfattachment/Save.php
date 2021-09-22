<?php
namespace Dckap\Attachment\Controller\Adminhtml\pdfattachment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{

    protected $pdfattachmentFactory;
    protected $uploaderFactory;
    protected $adapterFactory;
    protected $filesystem;
    protected $session;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Dckap\Attachment\Model\PdfattachmentFactory $pdfattachmentFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Model\Session $session
    ) {
        $this->pdfattachmentFactory = $pdfattachmentFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->session = $session;
        parent::__construct($context);
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
            $model = $this->pdfattachmentFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }

            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'attachment']);
                $uploader->setAllowedExtensions(['pdf', 'docx', 'doc', 'csv', 'xlsx', 'xls']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->adapterFactory->create();
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('attachment'));
                if ($result['error'] == 0) {
                    $data['attachment'] = 'attachment'.$result['file'];
                    $data['file_type'] = $uploader->getFileExtension();
                }
            if (isset($data['attachment']['delete']) && $data['attachment']['delete'] == '1') {
                $data['attachment'] = '';
            }
            if (isset($data['id']) && $data['id'] == '') {
                unset($data['id']);
            }
            $model->setData($data);
            $model->save();
            $this->messageManager->addSuccess(__('The Pdfattachment has been saved.'));
            $this->session->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Pdfattachment.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
