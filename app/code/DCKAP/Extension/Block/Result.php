<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DCKAP\Extension\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;

/**
 * Product search result block
 *
 * @api
 * @since 100.0.2
 */
class Result extends \Magento\CatalogSearch\Block\Result
{
    public function __construct(Context $context, LayerResolver $layerResolver, Data $catalogSearchData,
                                QueryFactory $queryFactory, array $data = [])
    {
        parent::__construct($context, $layerResolver, $catalogSearchData, $queryFactory, $data);
    }
    /**
     * Set search available list orders
     *
     * @return $this
     */
    public function setListOrders()
    {
        $category = $this->catalogLayer->getCurrentCategory();
        /* @var $category Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders['relevance'] = __('Relevance');

        $this->getListBlock()->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            'desc'
        )->setDefaultSortBy(
            'relevance'
        );

        return $this;
    }
}
