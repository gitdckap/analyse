<?php

namespace Dckap\Attachment\Controller\Adminhtml\pdfattachment;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $pdfAttachment;
    protected $session;


    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Dckap\Attachment\Model\Pdfattachment $pdfAttachment,
        \Magento\Backend\Model\Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->pdfAttachment = $pdfAttachment;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dckap_Attachment::Pdfattachment')
            ->addBreadcrumb(__('Dckap Attachment'), __('Dckap Attachment'))
            ->addBreadcrumb(__('Manage Attachment'), __('Manage Attachment'));
        return $resultPage;
    }

    /**
     * Edit Item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        // 2. Initial checking
        if ($id) {
            $this->pdfAttachment->load($id);
            if (!$this->pdfAttachment->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $this->pdfAttachment->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('pdfattachment', $this->pdfAttachment);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('Dckap_Attachment::pdfattachment');
        $resultPage->addBreadcrumb(__('Dckap'), __('Dckap'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Attachment') : __('New Attachment'),
            $id ? __('Edit Attachment') : __('New Attachment')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Attachment') : __('New Attachment'));
        //$resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Item'));

        return $resultPage;
    }
}
