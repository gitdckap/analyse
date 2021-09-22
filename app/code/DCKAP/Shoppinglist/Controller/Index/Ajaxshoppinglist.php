<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

class Ajaxshoppinglist extends \Magento\Framework\App\Action\Action
{
    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    
    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var  \DCKAP\Shoppinglist\Model\ShoppinglistFactory
     */
    protected $shoppinglistFactory;
    
    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var  \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->shoppinglistFactory = $shoppinglistFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $context->getMessageManager();
        $this->url = $context->getUrl();
        $this->_resource = $resource;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $customerSession = $this->customerSession->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
            $productId = $this->getRequest()->getParam('id');
            $storeId = $this->storeManager->getStore()->getId();

            $shoppingItem = $this->_resource->getTableName('shopping_list_item');

            $shoppinglist = $this->shoppinglistFactory->create();
            $shoppinglistCollection = $shoppinglist->getCollection()
                                    ->addFieldToFilter('main_table.customer_id', ['eq' => $customerId])
                                    ->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);

            $shoppinglistCollection->getSelect()
                                    ->join(
                                        ['slitem' => $shoppingItem],
                                        'main_table.list_id = slitem.shopping_list_id',
                                        ['shopping_list_item_id' => 'slitem.shopping_list_item_id']
                                    );

            $shoppinglistCollection->getSelect()->where("slitem.product_id=".$productId);
            $results = $shoppinglistCollection->getData();

            $tempResult = [];

            //Existing Shopping list Ids
            $existSlid = [];

            //Contains Shopping List Item Id (Used in Delete product)
            $tempItem = [];
            $j=0;

            foreach ($results as $_result) {
                $tempResult[$_result['list_id']] = $_result['list_name'];
                $existSlid[$j] = $_result['list_id'];
                $tempItem[$_result['list_id']] = $_result['shopping_list_item_id'];
                $j++;
            }

            //print_r($existSlid);

            if (!empty($existSlid)) {
                $customerSession->setExistingShoppingList($existSlid);
            } else {
                $customerSession->setExistingShoppingList(null);
            }

            //print_r($customerSession->getExistingShoppingList());

            if (!empty($tempItem)) {
                $customerSession->setShoppingListItem($tempItem);
            } else {
                $customerSession->setShoppingListItem(null);
            }

            $shoppinglist = $this->shoppinglistFactory->create();
            $shoppinglistCollection = $shoppinglist->getCollection()
                                    ->addFieldToFilter('customer_id', ['eq' => $customerId])
                                    ->addFieldToFilter('store_id', ['eq' => $storeId]);
            $collection = $shoppinglistCollection->getData();

            $tempArray = [];
            $i = 0;

            foreach ($collection as $_collection) {
                $tempArray[$i]['list_id'] = $_collection['list_id'];
                $tempArray[$i]['list_name'] = $_collection['list_name'];

                if (array_key_exists($_collection['list_id'], $tempResult)) {
                    $tempArray[$i]['is_select'] = true;
                } else {
                    $tempArray[$i]['is_select'] = false;
                }
                $i++;
            }

            $finalResult = json_encode($tempArray);

            return $this->resultJsonFactory->create()->setData($finalResult);
        } else {
            return false;
        }
    }
}
