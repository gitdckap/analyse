<?php
namespace DCKAP\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class SetAdditionalOptions implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;
    protected $serializer;
    protected $dckapCatalogHelper;
    protected $orderItemRepository;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if ($this->_request->getFullActionName() == 'checkout_cart_add') {
            $params = $this->_request->getParams();
            if (isset($params['custom_uom']) && $params['custom_uom'] != '') {
                $additionalOptions['custom_uom'] = [
                    'label' => 'UOM',
                    'value' => $params['custom_uom'],
                ];
            } else {
                if ($product->getTypeId() == 'grouped') {
                    // no logic to do for grouped
                } else {
                    $additionalOptions['custom_uom'] = [
                    'label' => 'UOM',
                    'value' => 'EA',
                    ];
                }
            }
            if ($product->getTypeId() != 'grouped') {
                $observer->getProduct()->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
            }
        }

        /* Adding product from shopping list */
        if ($this->_request->getFullActionName() == 'shoppinglist_index_addproducttocart') {
            $params = $this->_request->getParams();
            $uom = 'EA';
            $sku = $observer->getProduct()->getSku();
            $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
            if ($sessionProductData && isset($sessionProductData[$sku]) && isset($sessionProductData[$sku]['lineItem']['uom']['uomCode'])) {
                $uom = $sessionProductData[$sku]['lineItem']['uom']['uomCode'];
            }
            $additionalOptions['custom_uom'] = [
                'label' => 'UOM',
                'value' => $uom,
            ];

            $observer->getProduct()->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        }
    }
}
