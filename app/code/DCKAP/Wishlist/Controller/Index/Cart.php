<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DCKAP\Wishlist\Controller\Index;

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Add wishlist item to shopping cart and remove from wishlist controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends \Magento\Wishlist\Controller\Index\Cart
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    protected $serializer;

    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Wishlist\Model\Item\OptionFactory $optionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Wishlist\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        $this->itemFactory = $itemFactory;
        $this->cart = $cart;
        $this->optionFactory = $optionFactory;
        $this->productHelper = $productHelper;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->cartHelper = $cartHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $wishlistProvider,
            $quantityProcessor,
            $itemFactory,
            $cart,
            $optionFactory,
            $productHelper,
            $escaper,
            $helper,
            $cartHelper,
            $formKeyValidator
        );
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $itemId = (int)$this->getRequest()->getParam('item');
        /* @var $item \Magento\Wishlist\Model\Item */
        $item = $this->itemFactory->create()->load($itemId);
        if (!$item->getId()) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            $resultRedirect->setPath('*/*');
            return $resultRedirect;
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        $postQty = $this->getRequest()->getPostValue('qty');
        if ($postQty !== null && $qty !== $postQty) {
            $qty = $postQty;
        }
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->quantityProcessor->process($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        //set additional options for uom
        $custom_uom = $this->getRequest()->getParam('custom_uom');
        $postCustom_uom = $this->getRequest()->getPostValue('custom_uom');
        if ($postCustom_uom !== null && $custom_uom !== $postCustom_uom) {
            $custom_uom = $postCustom_uom;
        }
        if (is_array($custom_uom)) {
            if (isset($custom_uom[$itemId])) {
                $custom_uom = $custom_uom[$itemId];
            } else {
                $custom_uom = 'EA';
            }
        }
        $additionalOptions['custom_uom'] = [
            'label' => 'UOM',
            'value' => $custom_uom
        ];
      
        $item->addOption([
            'code' => 'additional_options',
            'value' => $this->serializer->serialize($additionalOptions),
            'product_id' => $item->getProduct()->getId()
        ]);

        $redirectUrl = $this->_url->getUrl('*/*');
        $configureUrl = $this->_url->getUrl(
            '*/*/configure/',
            [
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
            ]
        );

        try {
            /** @var \Magento\Wishlist\Model\ResourceModel\Item\Option\Collection $options */
            $options = $this->optionFactory->create()->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                ['current_config' => $item->getBuyRequest()]
            );

            $item->mergeBuyRequest($buyRequest);
            $item->addToCart($this->cart, true);
            $this->cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccess($message);
            }

            if ($this->cartHelper->getShouldRedirectToCart()) {
                $redirectUrl = $this->cartHelper->getCartUrl();
            } else {
                $refererUrl = $this->_redirect->getRefererUrl();
                if ($refererUrl && $refererUrl != $configureUrl) {
                    $redirectUrl = $refererUrl;
                }
            }
        } catch (ProductException $e) {
            $this->messageManager->addErrorMessage(__('This product(s) is out of stock.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            $redirectUrl = $configureUrl;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add the item to the cart right now.'));
        }

        $this->helper->calculate();

        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(['backUrl' => $redirectUrl]);
            return $resultJson;
        }

        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
