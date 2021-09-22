<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use FME\GoogleMapsStoreLocator\Block\Adminhtml\Storelocator\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PageActions
 */
class Actions extends Column
{
    /** Url path */
    const STORELOCATOR_URL_PATH_EDIT = 'googlemapsstorelocator/storelocator/edit';
    const STORELOCATOR_URL_PATH_DELETE = 'googlemapsstorelocator/storelocator/delete';

    /** @var UrlBuilder */
    protected $actionUrlBuilder;
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;
    private $_storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        $editUrl = self::STORELOCATOR_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $strStoreViews = null;
        $stores_list = $this->_storeManager->getStores(true, true);
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['gmaps_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['gmaps_id' => $item['gmaps_id']]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::STORELOCATOR_URL_PATH_DELETE,
                            ['gmaps_id' => $item['gmaps_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete \"${ $.$data.store_name }\"'),
                            'message' => __('Are you sure you wan\'t to delete a \"${ $.$data.store_name }\" record?')
                        ]
                    ];

                    if($item['store_id']){
                    $arrStoreViews = ( array )\GuzzleHttp\json_decode($item['store_id'], true);
                    foreach ($stores_list as $storekey => $storevalue) {
                        if ($storevalue->getIsActive() && in_array($storevalue->getStoreId(), $arrStoreViews)) {
                            $strStoreViews = $strStoreViews . ' ' . $storevalue->getName() . ',';
                        }
                    }
                }
                    $item['store_view'] = rtrim($strStoreViews, ',');
                    $strStoreViews = null;
                }

            }
        }

        return $dataSource;
    }
}
