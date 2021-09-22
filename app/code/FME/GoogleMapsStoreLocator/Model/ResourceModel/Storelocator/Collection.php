<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator;

use \FME\GoogleMapsStoreLocator\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'gmaps_id';

    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init(
            'FME\GoogleMapsStoreLocator\Model\Storelocator',
            'FME\GoogleMapsStoreLocator\Model\ResourceModel\Storelocator'
        );
        $this->_map['fields']['gmaps_id'] = 'main_table.gmaps_id';
    }
}
