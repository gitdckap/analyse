<?php

namespace Cloras\Base\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class Base extends Template
{
   

    private $wishlistProvider;
    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->wishlistProvider = $wishlistProvider;
        $this->requestInterface = $requestInterface;
        $this->saleList = $saleList;
    }


    public function getWishlistProductIds()
    {
        $currentUserWishlist = $this->wishlistProvider->getWishlist();
        if ($currentUserWishlist) {
            $wishlistItems = $currentUserWishlist->getItemCollection();
            $productIds = [];
            foreach ($wishlistItems as $wishlistItem) {
                $productIds[] = $wishlistItem->getProductId();
            }
            return $productIds;
        }
    }

    public function getCurrentActionName()
    {
        return $this->requestInterface->getFullActionName();
    }

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
}
