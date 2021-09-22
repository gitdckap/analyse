<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Model\Storelocator;

use FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var CollectionFactory
     */

    protected $collection;

    /**
     * @var StoreManagerInterface
     */

    protected $storeManager;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    const ALL_STORE_VIEWS = '0';

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $strStoreViews = '';
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        foreach ($items as $page) {
            $arrStoreDetails= $page->getData();
            $strStoreViews = ( false == empty( $arrStoreDetails['store_id'] ) ) ? implode(',', ( array ) \GuzzleHttp\json_decode($arrStoreDetails['store_id'],true)) : SELF::ALL_STORE_VIEWS;
            $this->loadedData[$page->getId()] = $page->getData();
            $this->loadedData[$page->getId()]['store_id'] = $strStoreViews;
        }

        $data = $this->dataPersistor->get('fme_googlemapsstorelocator');
        if (!empty($data)) {
            $page = $this->collection->getNewEmptyItem();

            $page->setData($data);
            $this->loadedData[$page->getId()] = $page->getData();
            $this->dataPersistor->clear('fme_googlemapsstorelocator');
        }
        return $this->loadedData;
    }
}
