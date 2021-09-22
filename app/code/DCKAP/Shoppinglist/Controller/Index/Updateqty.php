<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace DCKAP\Shoppinglist\Controller\Index;

class Updateqty extends \Magento\Framework\App\Action\Action
{

    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
    ) {

        $this->customerSession = $customerSession;
        $this->productlistFactory = $productlistFactory;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function execute()
    {
        //print_r($this->getRequest()->getParams());exit;
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            $shoppinglistItemId = $this->getRequest()->getParam('shoppinglist_item_id');
            $qty = (int) $this->getRequest()->getParam('qty');
            if ($qty == 0) {
                $qty = 1;
            }
            $item = $this->productlistFactory->create()->load($shoppinglistItemId);
            try {
                $item->setData('qty', $qty)->save();
                $this->messageManager->addSuccessMessage("Quantity has been updated");
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }
}
