<?php

namespace DCKAP\ShippmentApi\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;

    protected $shipconfig;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Shipping\Model\Config $shipconfig
    ) {
        $this->shipconfig=$shipconfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    public function getshippingmethods() {

        $activeCarriers = $this->shipconfig->getAllCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {
           $options = [];
           if( $carrierMethods = $carrierModel->getAllowedMethods() )
           {
               foreach ($carrierMethods as $methodCode => $method)
               {
                    $code= $carrierCode.'_'.$methodCode;
                    $options[] = ['value'=>$code,'label'=>$method];
               }
               $carrierTitle = $this->scopeConfig->getValue('carriers/'.$carrierCode.'/title');
           }
            $methods[] = ['value'=>$options,'label'=>$carrierTitle];
        }
        foreach ($methods as $carrier){
            $carreirs = [];
              foreach ($carrier['value'] as $child){ 
                    $carreirs[$child['value']]=(string)$child['label'];
              }
               if(!empty($carreirs)){
                 $method['shipping'][$carrier['label']]=$carreirs;
             }
        }
        //return json_encode($method);
        return $method;
    }
}
