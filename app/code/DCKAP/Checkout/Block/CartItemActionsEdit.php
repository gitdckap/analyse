<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Checkout\Block;

/**
 * @api
 * @since 100.0.2
 */
class CartItemActionsEdit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        if ($this->getItem()->getRedirectUrl()) {
            return $this->getItem()->getRedirectUrl();
        }

        $product = $this->getItem()->getProduct();
        $option = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $product->getUrlModel()->getUrl($product);
    }
}
