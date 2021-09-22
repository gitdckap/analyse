<?php

namespace Cloras\Base\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class Price extends Template
{
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }


    /**
     * Return the Price Url.
     *
     * @return string
     */
    public function getBasePriceUrl()
    {
        return $this->_urlBuilder->getUrl(
            'cloras/index/price',
            ['_secure' => true]
        );
    }


    /**
     * Return the Inventory Url.
     *
     * @return string
     */
    public function getBaseInventoryUrl()
    {
        return $this->_urlBuilder->getUrl(
            'cloras/index/inventory',
            ['_secure' => true]
        );
    }
}
