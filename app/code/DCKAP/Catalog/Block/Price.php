<?php

namespace DCKAP\Catalog\Block;

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
        \Magento\Framework\App\RequestInterface $requestInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->requestInterface = $requestInterface;
    }

    /**
     * Return the Price Url.
     *
     * @return string
     */
    public function getBasePriceUrl()
    {
        return $this->_urlBuilder->getUrl(
            'Catalog/index/price',
            ['_secure' => true]
        );
    }

    public function getFullActionName()
    {
        return $this->requestInterface->getFullActionName();
    }
}
