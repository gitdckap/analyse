<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\ShippingAdditionalFields\Helper;

use FME\GoogleMapsStoreLocator\Model\StorelocatorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

	protected $storelocator;
	protected $storeManager;
 
	 public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StorelocatorFactory $storelocator,
        StoreManagerInterface $storeManager
    ) {
       	$this->storelocator  = $storelocator;
         $this->storeManager = $storeManager;
         parent::__construct($context);
    }

    public function getWarehouse(){
        $arrPreferredWareHouse = [];
        $storeId = $this->storeManager->getStore()->getId();
        $arrStoreDetails = ( $this->storelocator->create()->getCollection()->addFieldToSelect(['gmaps_id','store_name','store_id','is_active']) )->getData();
       foreach($arrStoreDetails as $arrStoreDetail){
            if( $arrStoreDetail['is_active'] && isset($arrStoreDetail['store_id']) ){
                $arrStoreViews = ( array ) \GuzzleHttp\json_decode($arrStoreDetail['store_id'],true);
               if( true == in_array($storeId, $arrStoreViews) ){
                   array_push($arrPreferredWareHouse , $arrStoreDetail);
                }
            }

        }
        return $arrPreferredWareHouse;
    }

    public function getWarehouseDetail(){
        return $this->storelocator->create()->getCollection();
    }
}