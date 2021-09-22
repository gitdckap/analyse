<?php

namespace FME\GoogleMapsStoreLocator\Model\ResourceModel;

/**
 * Class Storelocator
 * @package FME\GoogleMapsStoreLocator\Model\ResourceModel
 */
class Storelocator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('fme_googlemapsstorelocator', 'gmaps_id');
    }
}
