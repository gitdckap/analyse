<?php

namespace Cloras\Base\Ui\Component\Listing\Column;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderGrid extends Column
{
    private $orderRepository;

    private $searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }//end __construct()

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $order      = $this->orderRepository->get($item['entity_id']);
                $extOrderId = $order->getData('ddi_order_id');

                // $this->getData('name') returns the name of the column so in this case it would return ext_order_id
                $item[$this->getData('name')] = $extOrderId;
            }
        }

        return $dataSource;
    }//end prepareDataSource()
}//end class
