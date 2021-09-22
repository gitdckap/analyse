<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Megamenu\Controller\Adminhtml\Category;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Megamenu\Model\MenuStores
     */
    protected $menuStores;

    /**
     * @var \Bss\Megamenu\Model\ResourceModel\MenuStores\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Result forward factory
     *
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\Megamenu\Model\MenuStores $menuStores
     * @param \Bss\Megamenu\Model\ResourceModel\MenuStores\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Bss\Megamenu\Model\MenuStores $menuStores,
        \Bss\Megamenu\Model\ResourceModel\MenuStores\CollectionFactory $collectionFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->backendSession = $context->getSession();
        $this->menuStores = $menuStores;
        $this->collectionFactory = $collectionFactory;
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$data) {
            $resultRedirect->setPath('*/*/new');
            return $resultRedirect;
        }

        try {
            $storeId = $data['store_id'];
            $storeCollection = $this->collectionFactory->create();
            $storeCollection->addFieldToFilter('store_id', $storeId);

            if ($storeCollection->getSize()) {
                $itemName = $storeCollection->getLastItem()->getName();
                $resultRedirect->setPath('*/*/new');
                $this->messageManager->addErrorMessage(
                    __('This menu store has already exist. Please check item with name: "%1"', $itemName)
                );
                return $resultRedirect;
            }

            $categoryStore = $this->menuStores;
            $categoryStore->setData($data);

            if (isset($data['id'])) {
                $categoryStore->setId($data['id']);
            }

            $categoryStore->save();
            $this->messageManager->addSuccessMessage(__('Menu has been successfully saved.'));
            $this->backendSession->setBssBlogcommentData(false);

            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'id' => $categoryStore->getId(),
                        '_current' => true
                    ]
                );

                return $resultRedirect;
            }

            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $categoryStore = $this->menuStores;
        $this->backendSession->setBssMegamenuStoreData($data);
        $resultRedirect->setPath(
            '*/*/edit',
            [
                'id' => $categoryStore->getId(),
                '_current' => true
            ]
        );

        return $resultRedirect;
    }
}
